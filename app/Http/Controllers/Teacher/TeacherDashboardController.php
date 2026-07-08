<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dashboard\TeacherDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class TeacherDashboardController extends Controller
{
    /**
     * Exibe o dashboard do professor com métricas e desempenho de turmas/alunos.
     */
    public function __invoke(TeacherDashboardService $dashboard): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('Teacher/Dashboard', [
            'metrics' => $dashboard->getMetrics($user),
            'classroomPerformance' => $dashboard->getClassroomPerformance($user),
            'studentPerformance' => $dashboard->getStudentPerformance($user),
        ]);
    }
}
