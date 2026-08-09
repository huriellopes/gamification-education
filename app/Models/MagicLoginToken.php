<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $selector Parte pública/indexada do token, usada para localizar a linha.
 * @property string $token Hash sha256 do verificador — nunca o valor em texto puro (ver MagicLoginService).
 */
class MagicLoginToken extends Model
{
    protected $fillable = [
        'user_id',
        'selector',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
