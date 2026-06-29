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
