<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createRequest(User $user, string $subject, string $message): self
    {
        return self::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    public function submitReply(string $replyText): void
    {
        $this->update([
            'reply' => $replyText,
            'status' => 'answered',
            'replied_at' => now(),
        ]);
    }
}
