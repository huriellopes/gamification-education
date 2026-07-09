<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Health;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use App\Services\Dashboard\Health\Concerns\BuildsHealthReport;

/**
 * Saúde de integridade escopada ao administrador: cobre as instituições,
 * professores, alunos e matérias aos quais ele tem acesso (todas as
 * instituições que gerencia via pivot institution_user).
 */
class AdminHealthService
{
    use BuildsHealthReport;

    /**
     * @param  list<int>  $institutionIds  Instituições que o admin gerencia.
     * @return array{
     *     checks: list<array{key: string, status: string, color: string, value: int, route: string|null}>,
     *     summary: array{status: string, color: string, alerts: int, critical: int, warning: int, ok: int, total: int}
     * }
     */
    public function report(array $institutionIds): array
    {
        if ($institutionIds === []) {
            return $this->buildReport([]);
        }

        return $this->buildReport([
            $this->institutionsWithoutSubject($institutionIds),
            $this->teachersWithoutSubject($institutionIds),
            $this->studentsWithoutClassroom($institutionIds),
            $this->subjectsWithoutClassroom($institutionIds),
        ]);
    }

    /**
     * Instituições do admin que ainda não têm nenhuma matéria cadastrada.
     *
     * @param  list<int>  $ids
     */
    private function institutionsWithoutSubject(array $ids): HealthCheck
    {
        $count = Institution::query()
            ->whereIn('id', $ids)
            ->whereDoesntHave('subjects')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'institutions_without_subject',
            value: $count,
            warnAt: 1,
            criticalAt: PHP_INT_MAX,
            routeName: 'admin.subjects.index',
        );
    }

    /**
     * Professores ativos sem nenhuma matéria vinculada (ociosos).
     *
     * @param  list<int>  $ids
     */
    private function teachersWithoutSubject(array $ids): HealthCheck
    {
        $count = User::query()
            ->where('role', UserRole::TEACHER)
            ->where('is_active', GeneralStatus::ACTIVE)
            ->where(function ($query) use ($ids): void {
                $query->whereIn('institution_id', $ids)
                    ->orWhereHas('institutions', fn ($sub) => $sub->whereIn('institutions.id', $ids));
            })
            ->whereDoesntHave('subjects')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'teachers_without_subject',
            value: $count,
            warnAt: 1,
            criticalAt: 10,
            routeName: 'admin.users.index',
        );
    }

    /**
     * Alunos ativos da(s) instituição(ões) sem matrícula em turma.
     *
     * @param  list<int>  $ids
     */
    private function studentsWithoutClassroom(array $ids): HealthCheck
    {
        $count = User::query()
            ->where('role', UserRole::STUDENT)
            ->where('is_active', GeneralStatus::ACTIVE)
            ->whereIn('institution_id', $ids)
            ->whereDoesntHave('enrolledClassrooms')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'students_without_classroom',
            value: $count,
            warnAt: 1,
            criticalAt: 25,
            routeName: 'admin.users.index',
        );
    }

    /**
     * Matérias órfãs (sem turma) — inacessíveis aos alunos.
     *
     * @param  list<int>  $ids
     */
    private function subjectsWithoutClassroom(array $ids): HealthCheck
    {
        $count = Subject::query()
            ->whereIn('institution_id', $ids)
            ->whereNull('classroom_id')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'subjects_without_classroom',
            value: $count,
            warnAt: 1,
            criticalAt: 10,
            routeName: 'admin.subjects.index',
        );
    }
}
