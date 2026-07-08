<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Health;

use App\Enums\HealthStatus;

/**
 * Resultado imutável de um único indicador de saúde/integridade.
 *
 * O rótulo e a descrição não vivem aqui de propósito: são resolvidos no
 * frontend via i18n (`superadmin.health.<key>.*`), mantendo o serviço livre de
 * apresentação e coerente com o restante do dashboard.
 */
final readonly class HealthCheck
{
    /**
     * @param  string  $key  Slug estável usado para i18n e como identificador.
     * @param  int  $value  Quantidade de itens problemáticos (0 = tudo certo).
     * @param  string|null  $routeName  Rota Ziggy para a tela de detalhe.
     */
    public function __construct(
        public string $key,
        public HealthStatus $status,
        public int $value,
        public ?string $routeName = null,
    ) {}

    /**
     * Cria um check derivando o status a partir de limiares.
     *
     * `$value` é a contagem de itens em situação irregular; quanto maior, pior.
     */
    public static function fromThresholds(
        string $key,
        int $value,
        int $warnAt = 1,
        int $criticalAt = PHP_INT_MAX,
        ?string $routeName = null,
    ): self {
        $status = match (true) {
            $value >= $criticalAt => HealthStatus::CRITICAL,
            $value >= $warnAt => HealthStatus::WARNING,
            default => HealthStatus::OK,
        };

        return new self($key, $status, $value, $routeName);
    }

    public function isHealthy(): bool
    {
        return $this->status === HealthStatus::OK;
    }

    /**
     * @return array{key: string, status: string, color: string, value: int, route: string|null}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'status' => $this->status->value,
            'color' => $this->status->color(),
            'value' => $this->value,
            'route' => $this->routeName,
        ];
    }
}
