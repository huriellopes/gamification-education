<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Models\User;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Models\Audit;

/**
 * Leitura do log de auditoria (owen-it/laravel-auditing) para o Super Admin.
 */
class AuditLogService
{
    /**
     * Registros de auditoria mais recentes, já formatados para o frontend.
     *
     * O acesso aos campos é feito via getAttribute() porque o model do pacote
     * não declara @property; e o nome do usuário é resolvido em lote (sem usar
     * a relação polimórfica do model do vendor).
     *
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit = 200): array
    {
        $audits = Audit::query()->latest()->limit($limit)->get();

        $names = User::query()
            ->whereIn('id', $audits->pluck('user_id')->filter()->unique()->all())
            ->pluck('name', 'id');

        return $audits->map(function (Audit $audit) use ($names): array {
            $createdAt = $audit->getAttribute('created_at');

            return [
                'id' => $audit->getKey(),
                'user' => $names->get($audit->getAttribute('user_id')) ?? '—',
                'event' => $audit->getAttribute('event'),
                'auditable_type' => class_basename((string) $audit->getAttribute('auditable_type')),
                'auditable_id' => $audit->getAttribute('auditable_id'),
                'old_values' => $audit->getAttribute('old_values'),
                'new_values' => $audit->getAttribute('new_values'),
                'ip_address' => $audit->getAttribute('ip_address'),
                'created_at' => $createdAt instanceof Carbon ? $createdAt->toIso8601String() : null,
            ];
        })->all();
    }
}
