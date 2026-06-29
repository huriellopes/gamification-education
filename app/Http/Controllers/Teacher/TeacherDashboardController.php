<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class TeacherDashboardController extends Controller
{
    /**
     * Exibe o dashboard do professor com métricas e desempenho de turmas/alunos.
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('Teacher/Dashboard', [
            'metrics' => [
                'classrooms_count' => $user->classrooms()->count(),
                'students_count' => User::query()
                    ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $user->id))
                    ->count(),
                'subjects_count' => Subject::query()
                    ->whereHas('classroom', fn ($query) => $query->where('teacher_id', $user->id))
                    ->count(),
            ],
            'classroomPerformance' => $user->classrooms()
                ->orderBy('name')
                ->get()
                ->map(fn ($classroom) => [
                    'name' => $classroom->name,
                    'students_count' => $classroom->students()->count(),
                    'average_points' => (int) round((float) $classroom->students()->avg('points')),
                ])
                ->values(),
            'studentPerformance' => User::query()
                ->students()
                ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $user->id))
                ->orderByDesc('points')
                ->limit(10)
                ->get(['id', 'name', 'points'])
                ->map(fn ($student) => [
                    'name' => $student->name,
                    'points' => (int) $student->points,
                ])
                ->values(),
        ]);
    }
}
