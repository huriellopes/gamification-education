<?php

declare(strict_types=1);

namespace App\Models;

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

    public static function recordVisit(string $ip, ?string $userAgent): void
    {
        self::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
