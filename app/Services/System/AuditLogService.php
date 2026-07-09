<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Models\Audit;

/**
 * Leitura do log de auditoria (owen-it/laravel-auditing) para o Super Admin.
 */
class AuditLogService
{
    /**
     * Registros de auditoria paginados, já formatados para o frontend.
     *
     * O acesso aos campos é feito via getAttribute() porque o model do pacote
     * não declara @property; e o nome do usuário é resolvido em lote (sem usar
     * a relação polimórfica do model do vendor). Busca e ordenação ocorrem no
     * banco, sobre colunas simples (o nome do usuário é derivado e não entra na
     * ordenação).
     *
     * @param  int  $perPage  Registros por página; -1 = todos.
     */
    public function paginated(int $perPage = 20, ?string $search = null, string $sort = 'created_at', string $direction = 'desc'): LengthAwarePaginator
    {
        $sort = in_array($sort, ['created_at', 'event'], true) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query = Audit::query()
            ->when(
                $search !== null && $search !== '',
                fn ($q) => $q->where('event', 'like', '%' . $search . '%')
                    ->orWhere('auditable_type', 'like', '%' . $search . '%'),
            )
            ->orderBy($sort, $direction);

        $perPage = $perPage < 1 ? max((clone $query)->count(), 1) : $perPage;

        $paginator = $query->paginate($perPage);

        $names = User::query()
            ->whereIn('id', $paginator->getCollection()->pluck('user_id')->filter()->unique()->all())
            ->pluck('name', 'id');

        return $paginator->through(fn (Audit $audit): array => [
            'id' => $audit->getKey(),
            'user' => $names->get($audit->getAttribute('user_id')) ?? '—',
            'event' => $audit->getAttribute('event'),
            'auditable_type' => class_basename((string) $audit->getAttribute('auditable_type')),
            'auditable_id' => $audit->getAttribute('auditable_id'),
            'old_values' => $audit->getAttribute('old_values'),
            'new_values' => $audit->getAttribute('new_values'),
            'ip_address' => $audit->getAttribute('ip_address'),
            'created_at' => ($createdAt = $audit->getAttribute('created_at')) instanceof Carbon
                ? $createdAt->toIso8601String()
                : null,
        ]);
    }
}
