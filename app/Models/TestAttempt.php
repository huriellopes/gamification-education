<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'test_id', 'score', 'correct_answers', 'total_questions', 'completed_at'])]
class TestAttempt extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Test, $this>
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * The student's best attempt at a given test, if any.
     */
    public static function bestForUserAndTest(int $userId, int $testId): ?self
    {
        return static::query()
            ->forUserAndTest($userId, $testId)
            ->orderByDesc('score')
            ->first();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForUserAndTest(Builder $query, int $userId, int $testId): Builder
    {
        return $query->where('user_id', $userId)->where('test_id', $testId);
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'score' => 'integer',
            'correct_answers' => 'integer',
            'total_questions' => 'integer',
        ];
    }
}
