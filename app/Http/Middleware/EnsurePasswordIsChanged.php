<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $routeName = Route::currentRouteName();
            $allowedRoutes = [
                'password.force-change',
                'password.force-change.update',
                'logout',
            ];

            if (!in_array($routeName, $allowedRoutes, true)) {
                return to_route('password.force-change');
            }
        }

        return $next($request);
    }
}
