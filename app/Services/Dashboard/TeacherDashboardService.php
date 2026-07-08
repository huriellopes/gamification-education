<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Subject;
use App\Models\User;

/**
 * Métricas do dashboard do professor.
 *
 * Extraído do controller para manter a lógica de consulta fora da camada HTTP
 * e alinhar o professor ao mesmo padrão de service das demais dashboards.
 */
class TeacherDashboardService
{
    /**
     * Contadores de topo (turmas, alunos e matérias do professor).
     *
     * @return array{classrooms_count: int, students_count: int, subjects_count: int}
     */
    public function getMetrics(User $teacher): array
    {
        return [
            'classrooms_count' => $teacher->classrooms()->count(),
            'students_count' => User::query()
                ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->count(),
            'subjects_count' => Subject::query()
                ->whereHas('classroom', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->count(),
        ];
    }

    /**
     * Desempenho por turma do professor (média de pontos dos alunos).
     *
     * @return list<array{name: string, students_count: int, average_points: int}>
     */
    public function getClassroomPerformance(User $teacher): array
    {
        return $teacher->classrooms()
            ->orderBy('name')
            ->get()
            ->map(fn ($classroom) => [
                'name' => $classroom->name,
                'students_count' => $classroom->students()->count(),
                'average_points' => (int) round((float) $classroom->students()->avg('points')),
            ])
            ->values()
            ->all();
    }

    /**
     * Top 10 alunos do professor por pontuação.
     *
     * @return list<array{name: string, points: int}>
     */
    public function getStudentPerformance(User $teacher): array
    {
        return User::query()
            ->students()
            ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->orderByDesc('points')
            ->limit(10)
            ->get(['id', 'name', 'points'])
            ->map(fn ($student) => [
                'name' => $student->name,
                'points' => (int) $student->points,
            ])
            ->values()
            ->all();
    }
}
