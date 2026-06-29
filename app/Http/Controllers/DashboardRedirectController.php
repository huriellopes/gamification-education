<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return to_route('super-admin.dashboard');
        }

        if ($user->isInstitutionAdmin()) {
            return to_route('admin.dashboard');
        }

        if ($user->isTeacher()) {
            return to_route('teacher.dashboard');
        }

        return to_route('student.dashboard');
    }
}
