<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;

class StudentStudyService
{
    /**
     * Study materials of a subject flagged with the student's completion state.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMaterialsProgress(Subject $subject, User $user): array
    {
        return $subject->studyMaterials()
            ->get()
            ->map(fn (StudyMaterial $material) => [
                'id' => $material->id,
                'title' => $material->title,
                'points_reward' => $material->points_reward,
                'completed' => $user->hasCompletedMaterial($material->id),
            ])
            ->all();
    }

    /**
     * Tests of a subject with the student's best attempt for each.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTestsProgress(Subject $subject, User $user): array
    {
        return $subject->tests()
            ->get()
            ->map(function (Test $test) use ($user) {
                $best = TestAttempt::bestForUserAndTest($user->id, $test->id);

                return [
                    'id' => $test->id,
                    'title' => $test->title,
                    'description' => $test->description,
                    'points_reward' => $test->points_reward,
                    'best_score' => $best?->score,
                    'correct_answers' => $best?->correct_answers,
                    'total_questions' => $best?->total_questions,
                ];
            })
            ->all();
    }
}
