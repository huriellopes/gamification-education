<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dashboard\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Exibe o dashboard do administrador da instituição.
     */
    public function __invoke(AdminDashboardService $dashboard): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = (int) $user->institution_id;

        return Inertia::render('Admin/Dashboard', [
            'stats' => $dashboard->getStats($institutionId),
            'performanceChart' => $dashboard->getPerformanceChart($institutionId),
            'students' => $dashboard->getStudents($institutionId),
            'teachers' => $dashboard->getTeachers($institutionId),
            'reports' => $dashboard->getReports((int) $user->id),
        ]);
    }
}
