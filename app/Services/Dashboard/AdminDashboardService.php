<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Http\Resources\UserResource;
use App\Models\Report;
use App\Models\ScoreHistory;
use App\Models\Subject;
use App\Models\User;
use App\Services\Concerns\BuildsDailyChart;

class AdminDashboardService
{
    use BuildsDailyChart;

    /**
     * Headline counters for the institution.
     *
     * @return array<string, int>
     */
    public function getStats(int $institutionId): array
    {
        return [
            'total_teachers' => User::query()->teachers()->forInstitution($institutionId)->count(),
            'total_subjects' => Subject::query()->forInstitution($institutionId)->count(),
            'total_students' => User::query()->students()->forInstitution($institutionId)->count(),
        ];
    }

    /**
     * Points earned by the institution's students over the last 7 days.
     *
     * @return list<array<string, int|string>>
     */
    public function getPerformanceChart(int $institutionId): array
    {
        $raw = ScoreHistory::dailyPointsSince(now()->subDays(6)->startOfDay(), $institutionId);

        return $this->dailyChart($raw, 'points');
    }

    /**
     * Students of the institution ordered by points (leaderboard).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStudents(int $institutionId): array
    {
        return UserResource::collection(
            User::query()
                ->students()
                ->forInstitution($institutionId)
                ->orderByDesc('points')
                ->get(),
        )->resolve();
    }

    /**
     * Teachers of the institution.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeachers(int $institutionId): array
    {
        return UserResource::collection(
            User::query()
                ->teachers()
                ->forInstitution($institutionId)
                ->get(),
        )->resolve();
    }

    /**
     * Reports requested by the given admin.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getReports(int $userId): array
    {
        return Report::query()
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->toArray();
    }
}
