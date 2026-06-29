<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Scope para modelos com a coluna institution_id.
 */
trait BelongsToInstitution
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForInstitution(Builder $query, int $institutionId): Builder
    {
        return $query->where('institution_id', $institutionId);
    }
}
