<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsStudent;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\EnsureUserIsTeacher;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Deploy atrás da Cloudflare (TLS terminado no edge). Sem confiar no
        // proxy, o Laravel enxerga a requisição como HTTP e grava/gera cookies
        // e URLs sem HTTPS — o que impede o navegador de reenviar o cookie
        // "remember" e derruba a sessão após ela expirar. Confiamos apenas nas
        // faixas de IP da Cloudflare (mais restritivo que "*").
        // Fonte: https://www.cloudflare.com/ips/ (revisar periodicamente)
        $middleware->trustProxies(at: [
            // IPv4
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22',
            '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
            '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22',
            '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            // IPv6
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32',
            '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29',
            '2c0f:f248::/32',
        ], headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureUserIsActive::class,
            EnsurePasswordIsChanged::class,
        ]);

        $middleware->alias([
            'role.super_admin' => EnsureUserIsSuperAdmin::class,
            'role.admin' => EnsureUserIsAdmin::class,
            'role.teacher' => EnsureUserIsTeacher::class,
            'role.student' => EnsureUserIsStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $renderError = fn () => Inertia::render('Error', [
                'status' => $response->getStatusCode(),
                // The web middleware (HandleInertiaRequests::share) does not run for
                // unmatched routes (404), so attach translations/locale explicitly.
                'locale' => app()->getLocale(),
                'translations' => HandleInertiaRequests::translations(),
            ])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());

            if (!app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [401, 403, 404, 500, 503, 419], true)) {
                return $renderError();
            } elseif (in_array($response->getStatusCode(), [401, 403, 404, 419], true)) {
                return $renderError();
            }

            return $response;
        });
    })->create();
