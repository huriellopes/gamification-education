<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScoreSource;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'points', 'source_type', 'source_id', 'description'])]
class ScoreHistory extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Total points earned per day since the given date, keyed by "Y-m-d".
     *
     * @return array<string, int>
     */
    public static function dailyPointsSince(CarbonInterface $since, ?int $institutionId = null): array
    {
        return static::query()
            ->when($institutionId !== null, fn (Builder $query) => $query->forInstitution($institutionId))
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, SUM(points) as total_points')
            ->groupBy('date')
            ->orderBy('date')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($item) => [(string) $item->date => (int) $item->total_points])
            ->all();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForInstitution(Builder $query, int $institutionId): Builder
    {
        return $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('institution_id', $institutionId));
    }

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'source_id' => 'integer',
            'source_type' => ScoreSource::class,
        ];
    }
}
