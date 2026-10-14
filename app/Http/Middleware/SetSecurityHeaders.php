<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeçalhos de segurança básicos, aplicados a toda resposta web.
 *
 * A aplicação não definia nenhum destes antes — sem eles, a única defesa
 * contra a página ser carregada num iframe malicioso (clickjacking) ou
 * contra MIME-sniffing dependia inteiramente de configuração externa
 * (Cloudflare/servidor), fora do controle do código.
 */
class SetSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // frame-ancestors reforça o X-Frame-Options (suportado por navegadores
        // mais novos). As demais diretivas só bloqueiam o que a aplicação não
        // usa (<base> injetado, plugins, formulários enviados a outro domínio);
        // script-src/style-src ficam livres para não quebrar fontes externas
        // (fonts.bunny.net), Google Ads da landing ou os assets do Vite.
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; object-src 'none'; form-action 'self'");
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // HSTS só faz sentido sobre HTTPS e em produção (em local quebraria o
        // acesso por HTTP ao localhost). Sem includeSubDomains de propósito:
        // não sabemos se todos os subdomínios do domínio servem HTTPS.
        if ($request->isSecure() && app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
