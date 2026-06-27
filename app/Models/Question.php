<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['test_id', 'question_text', 'options', 'correct_option_index'])]
class Question extends Model
{
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_option_index' => 'integer',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
