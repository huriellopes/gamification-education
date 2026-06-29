<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            if ($request->user()->isSuperAdmin()) {
                return to_route('super-admin.dashboard');
            }

            if ($request->user()->isInstitutionAdmin()) {
                return to_route('admin.dashboard');
            }

            if ($request->user()->isTeacher()) {
                return to_route('teacher.dashboard');
            }

            if ($request->user()->isStudent()) {
                return $next($request);
            }

            abort(403, 'Acesso não autorizado.');
        }

        abort(401);
    }
}
