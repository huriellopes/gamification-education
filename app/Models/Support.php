<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SupportStatus $status
 */
class Support extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'status' => SupportStatus::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
