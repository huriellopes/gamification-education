<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'test_id', 'score', 'correct_answers', 'total_questions', 'completed_at'])]
class TestAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'score' => 'integer',
            'correct_answers' => 'integer',
            'total_questions' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
