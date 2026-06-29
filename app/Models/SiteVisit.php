<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'visited_at',
    ];

    protected $casts = [
        'ip_address' => 'encrypted',
        'visited_at' => 'datetime',
    ];

    /**
     * Number of visits per day since the given date, keyed by "Y-m-d".
     *
     * @return array<string, int>
     */
    public static function dailyCountsSince(CarbonInterface $since): array
    {
        return static::query()
            ->where('visited_at', '>=', $since)
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($item) => [(string) $item->date => (int) $item->total])
            ->all();
    }
}
