<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Health;

use App\Enums\GeneralStatus;
use App\Enums\HealthStatus;
use App\Enums\SupportStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Consolida sinais de saúde operacional e de integridade de dados do sistema
 * para o dashboard do Super Admin.
 *
 * Cada check devolve a contagem de itens em situação irregular e um status
 * derivado por limiares. O relatório final já vem ordenado por severidade e
 * traz um resumo agregado (status geral + total de alertas).
 */
class SystemHealthService
{
    /**
     * Relatório completo: lista de checks (mais severos primeiro) + resumo.
     *
     * @return array{
     *     checks: list<array{key: string, status: string, color: string, value: int, route: string|null}>,
     *     summary: array{status: string, color: string, alerts: int, critical: int, warning: int, ok: int, total: int}
     * }
     */
    public function report(): array
    {
        $checks = $this->checks();

        usort(
            $checks,
            fn (HealthCheck $a, HealthCheck $b) => $b->status->weight() <=> $a->status->weight(),
        );

        return [
            'checks' => array_map(fn (HealthCheck $check) => $check->toArray(), $checks),
            'summary' => $this->summarize($checks),
        ];
    }

    /**
     * @return list<HealthCheck>
     */
    private function checks(): array
    {
        return [
            $this->failedJobs(),
            $this->unansweredSupports(),
            $this->institutionsWithoutActiveAdmin(),
            $this->classroomsWithoutTeacher(),
            $this->subjectsWithoutClassroom(),
            $this->activeStudentsWithoutClassroom(),
        ];
    }

    /**
     * Jobs em fila que falharam — sinaliza processamento assíncrono quebrado.
     */
    private function failedJobs(): HealthCheck
    {
        return HealthCheck::fromThresholds(
            key: 'failed_jobs',
            value: DB::table('failed_jobs')->count(),
            warnAt: 1,
            criticalAt: 10,
            routeName: 'super-admin.logs.index',
        );
    }

    /**
     * Chamados de suporte ainda pendentes de resposta.
     */
    private function unansweredSupports(): HealthCheck
    {
        return HealthCheck::fromThresholds(
            key: 'unanswered_supports',
            value: Support::query()->where('status', SupportStatus::PENDING)->count(),
            warnAt: 1,
            criticalAt: 10,
            routeName: 'super-admin.supports.index',
        );
    }

    /**
     * Instituições ativas sem nenhum administrador ativo — ficam sem gestão.
     */
    private function institutionsWithoutActiveAdmin(): HealthCheck
    {
        $count = Institution::query()
            ->where('is_active', GeneralStatus::ACTIVE)
            ->whereDoesntHave('users', function ($query): void {
                $query->where('role', UserRole::ADMIN)
                    ->where('is_active', GeneralStatus::ACTIVE);
            })
            ->count();

        return HealthCheck::fromThresholds(
            key: 'institutions_without_admin',
            value: $count,
            warnAt: 1,
            criticalAt: 3,
            routeName: 'super-admin.institutions.index',
        );
    }

    /**
     * Turmas sem professor responsável.
     */
    private function classroomsWithoutTeacher(): HealthCheck
    {
        return HealthCheck::fromThresholds(
            key: 'classrooms_without_teacher',
            value: Classroom::query()->whereNull('teacher_id')->count(),
            warnAt: 1,
            criticalAt: 10,
            routeName: 'super-admin.classrooms.index',
        );
    }

    /**
     * Matérias órfãs (sem turma vinculada).
     */
    private function subjectsWithoutClassroom(): HealthCheck
    {
        return HealthCheck::fromThresholds(
            key: 'subjects_without_classroom',
            value: Subject::query()->whereNull('classroom_id')->count(),
            warnAt: 1,
            criticalAt: 10,
            routeName: 'super-admin.subjects.index',
        );
    }

    /**
     * Alunos ativos que não estão matriculados em nenhuma turma.
     */
    private function activeStudentsWithoutClassroom(): HealthCheck
    {
        $count = User::query()
            ->where('role', UserRole::STUDENT)
            ->where('is_active', GeneralStatus::ACTIVE)
            ->whereDoesntHave('enrolledClassrooms')
            ->count();

        return HealthCheck::fromThresholds(
            key: 'students_without_classroom',
            value: $count,
            warnAt: 1,
            criticalAt: 25,
            routeName: 'super-admin.users.index',
        );
    }

    /**
     * @param  list<HealthCheck>  $checks
     * @return array{status: string, color: string, alerts: int, critical: int, warning: int, ok: int, total: int}
     */
    private function summarize(array $checks): array
    {
        $overall = HealthStatus::OK;
        $critical = 0;
        $warning = 0;
        $ok = 0;

        foreach ($checks as $check) {
            if ($check->status->weight() > $overall->weight()) {
                $overall = $check->status;
            }

            match ($check->status) {
                HealthStatus::CRITICAL => $critical++,
                HealthStatus::WARNING => $warning++,
                HealthStatus::OK => $ok++,
            };
        }

        return [
            'status' => $overall->value,
            'color' => $overall->color(),
            'alerts' => $critical + $warning,
            'critical' => $critical,
            'warning' => $warning,
            'ok' => $ok,
            'total' => count($checks),
        ];
    }
}
