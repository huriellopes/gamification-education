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
        // mais novos); não restringimos outras diretivas para não quebrar
        // fontes externas (fonts.bunny.net) ou os assets do Vite.
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");

        return $response;
    }
}
