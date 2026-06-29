<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scope para modelos com a coluna is_active (cast para GeneralStatus).
 */
trait Activatable
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', GeneralStatus::ACTIVE);
    }
}
