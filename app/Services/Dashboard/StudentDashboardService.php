<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ScoreHistory;
use App\Models\Subject;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\Ranking\RankingService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class StudentDashboardService
{
    public function __construct(private readonly RankingService $rankingService) {}

    /**
     * Subjects of the student's institution with per-subject progress.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSubjectsProgress(User $user): array
    {
        if ($user->institution_id === null) {
            return [];
        }

        // O aluno só enxerga as matérias (ativas) das turmas em que está matriculado.
        $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id');

        if ($classroomIds->isEmpty()) {
            return [];
        }

        return Subject::query()
            ->active()
            ->forInstitution($user->institution_id)
            ->whereIn('classroom_id', $classroomIds)
            ->with(['studyMaterials', 'tests'])
            ->get()
            ->map(function (Subject $subject) use ($user) {
                $totalMaterials = $subject->studyMaterials->count();
                $completedMaterials = $user->completedMaterials()
                    ->where('subject_id', $subject->id)
                    ->count();

                $bestScore = TestAttempt::query()
                    ->where('user_id', $user->id)
                    ->whereIn('test_id', $subject->tests->pluck('id'))
                    ->max('score') ?? 0;

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'description' => $subject->description,
                    'total_materials' => $totalMaterials,
                    'completed_materials' => $completedMaterials,
                    'best_test_score' => $bestScore,
                    'progress_percentage' => $totalMaterials > 0
                        ? round(($completedMaterials / $totalMaterials) * 100)
                        : 0,
                ];
            })
            ->all();
    }

    /**
     * The student's most recent score history entries.
     *
     * @return EloquentCollection<int, ScoreHistory>
     */
    public function getRecentScoreHistory(User $user, int $limit = 5): EloquentCollection
    {
        return ScoreHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Top of the global ranking, shaped for the sidebar widget.
     *
     * @return array<int, array{position: int, name: string, points: int, institution: string}>
     */
    public function getLeaderboard(int $limit = 5): array
    {
        return $this->rankingService->getGlobalRanking($limit)
            ->values()
            ->map(fn (User $rankUser, int $index) => [
                'position' => $index + 1,
                'name' => $rankUser->name,
                'points' => $rankUser->points,
                // institution_id is nullable for students, so the relation may be null at runtime.
                'institution' => $rankUser->institution?->name ?? 'N/A', // @phpstan-ignore nullsafe.neverNull
            ])
            ->all();
    }

    /**
     * Quick headline stats for the student.
     *
     * @return array<string, int>
     */
    public function getQuickStats(User $user): array
    {
        return [
            'points' => $user->points,
            'completed_materials_count' => $user->completedMaterials()->count(),
            'test_attempts_count' => $user->testAttempts()->count(),
        ];
    }
}
