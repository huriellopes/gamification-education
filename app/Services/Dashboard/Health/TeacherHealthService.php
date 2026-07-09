<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Health;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use App\Services\Dashboard\Health\Concerns\BuildsHealthReport;

/**
 * Saúde de integridade escopada ao professor: cobre as instituições, alunos,
 * turmas e matérias aos quais ele tem acesso (as turmas que leciona e as
 * matérias dessas turmas).
 */
class TeacherHealthService
{
    use BuildsHealthReport;

    /**
     * @return array{
     *     checks: list<array{key: string, status: string, color: string, value: int, route: string|null}>,
     *     summary: array{status: string, color: string, alerts: int, critical: int, warning: int, ok: int, total: int}
     * }
     */
    public function report(User $teacher): array
    {
        $classroomIds = Classroom::query()
            ->where('teacher_id', $teacher->id)
            ->pluck('id')
            ->all();

        return $this->buildReport([
            $this->institutionsWithoutClassroom($teacher),
            $this->classroomsWithoutStudents($teacher),
            $this->subjectsWithoutContent($classroomIds),
            $this->unengagedStudents($teacher),
        ]);
    }

    /**
     * Instituições às quais o professor pertence mas onde ele não leciona
     * nenhuma turma.
     */
    private function institutionsWithoutClassroom(User $teacher): HealthCheck
    {
        $institutionIds = $teacher->managedInstitutionIds();

        $withClassroom = Classroom::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('institution_id', $institutionIds)
            ->distinct()
            ->pluck('institution_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $count = count(array_diff($institutionIds, $withClassroom));

        return HealthCheck::fromThresholds(
            key: 'institutions_without_classroom',
            value: $count,
            warnAt: 1,
            criticalAt: PHP_INT_MAX,
            routeName: 'teacher.classrooms.index',
        );
    }

    /**
     * Turmas do professor sem nenhum aluno matriculado.
     */
    private function classroomsWithoutStudents(User $teacher): HealthCheck
    {
        $count = Classroom::query()
            ->where('teacher_id', $teacher->id)
            ->whereDoesntHave('students')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'classrooms_without_students',
            value: $count,
            warnAt: 1,
            criticalAt: 10,
            routeName: 'teacher.classrooms.index',
        );
    }

    /**
     * Matérias das turmas do professor sem nenhum conteúdo (sem material de
     * estudo e sem prova) — o aluno não tem o que fazer.
     *
     * @param  list<int>  $classroomIds
     */
    private function subjectsWithoutContent(array $classroomIds): HealthCheck
    {
        $count = $classroomIds === []
            ? 0
            : Subject::query()
                ->whereIn('classroom_id', $classroomIds)
                ->whereDoesntHave('studyMaterials')
                ->whereDoesntHave('tests')
                ->count();

        return HealthCheck::fromThresholds(
            key: 'subjects_without_content',
            value: $count,
            warnAt: 1,
            criticalAt: 10,
            routeName: 'teacher.subjects.index',
        );
    }

    /**
     * Alunos ativos das turmas do professor que ainda não pontuaram (sem
     * engajamento).
     */
    private function unengagedStudents(User $teacher): HealthCheck
    {
        $count = User::query()
            ->where('role', UserRole::STUDENT)
            ->where('is_active', GeneralStatus::ACTIVE)
            ->where('points', 0)
            ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->count();

        return HealthCheck::fromThresholds(
            key: 'unengaged_students',
            value: $count,
            warnAt: 1,
            criticalAt: 25,
            routeName: 'teacher.students.index',
        );
    }
}
