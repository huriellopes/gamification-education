<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\GeneralStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->is_active === GeneralStatus::INACTIVE) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return to_route('login')->withErrors([
                    'email' => 'Sua conta está desativada. Entre em contato com o administrador.',
                ]);
            }

            if ($user->institution && $user->institution->getAttribute('is_active') === GeneralStatus::INACTIVE) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return to_route('login')->withErrors([
                    'email' => 'A instituição associada à sua conta está desativada.',
                ]);
            }
        }

        return $next($request);
    }
}
