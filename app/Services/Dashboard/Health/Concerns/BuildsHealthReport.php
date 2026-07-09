<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Health\Concerns;

use App\Enums\HealthStatus;
use App\Services\Dashboard\Health\HealthCheck;

/**
 * Monta o relatório de saúde a partir de uma lista de checks: ordena por
 * severidade (mais grave primeiro) e consolida o resumo agregado.
 *
 * Compartilhado entre os serviços de saúde por papel (Super Admin, Admin,
 * Professor) para manter idêntica a forma do payload consumido pelo frontend.
 */
trait BuildsHealthReport
{
    /**
     * @param  list<HealthCheck>  $checks
     * @return array{
     *     checks: list<array{key: string, status: string, color: string, value: int, route: string|null}>,
     *     summary: array{status: string, color: string, alerts: int, critical: int, warning: int, ok: int, total: int}
     * }
     */
    protected function buildReport(array $checks): array
    {
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
