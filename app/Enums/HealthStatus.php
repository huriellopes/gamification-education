<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Severidade de um indicador de saúde/integridade do sistema.
 *
 * Usado pelos health checks do dashboard do Super Admin para classificar cada
 * sinal e para consolidar o status geral (o mais severo vence).
 */
enum HealthStatus: string
{
    /**
     * Cor semântica usada nos badges/cards do frontend.
     */
    public function color(): string
    {
        return match ($this) {
            self::OK => 'emerald',
            self::WARNING => 'amber',
            self::CRITICAL => 'red',
        };
    }

    /**
     * Peso relativo — quanto maior, mais severo. Usado para ordenar os checks
     * e para derivar o status geral do relatório.
     */
    public function weight(): int
    {
        return match ($this) {
            self::OK => 0,
            self::WARNING => 1,
            self::CRITICAL => 2,
        };
    }
    case OK = 'ok';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
}
