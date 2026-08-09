<?php

declare(strict_types=1);

namespace App\Support;

class SensitiveDataRedactor
{
    /**
     * Chaves nunca enviadas ao frontend, mesmo dentro de blobs "brutos" onde
     * elas normalmente já estariam ocultas (ex.: o snapshot da lixeira, que o
     * pacote spatie/laravel-deleted-models grava desfazendo de propósito o
     * $hidden do model para permitir restauração completa).
     *
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    private const PLACEHOLDER = '[redacted]';

    /**
     * Redige, recursivamente, os valores de chaves sensíveis conhecidas em
     * qualquer nível do array. Preserva `null` como `null` (não finge que
     * havia um segredo onde não havia nenhum).
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public static function redact(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, self::SENSITIVE_KEYS, true)) {
                $result[$key] = $value === null ? null : self::PLACEHOLDER;

                continue;
            }

            $result[$key] = is_array($value) ? self::redact($value) : $value;
        }

        return $result;
    }
}
