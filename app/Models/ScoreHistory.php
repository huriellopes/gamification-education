<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'points', 'source_type', 'source_id', 'description'])]
class ScoreHistory extends Model
{
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'source_id' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
