<?php

declare(strict_types=1);

namespace App\Services\System;

use OwenIt\Auditing\Models\Audit;

/**
 * Leitura do log de auditoria (owen-it/laravel-auditing) para o Super Admin.
 */
class AuditLogService
{
    /**
     * Registros de auditoria mais recentes, já formatados para o frontend.
     *
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit = 200): array
    {
        return Audit::query()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Audit $audit): array => [
                'id' => $audit->id,
                'user' => $audit->user?->getAttribute('name') ?? '—',
                'event' => $audit->event,
                'auditable_type' => class_basename((string) $audit->auditable_type),
                'auditable_id' => $audit->auditable_id,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'ip_address' => $audit->ip_address,
                'created_at' => $audit->getAttribute('created_at')?->toIso8601String(),
            ])
            ->all();
    }
}
