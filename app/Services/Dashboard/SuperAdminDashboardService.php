<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\UserRole;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Http\Resources\UserResource;
use App\Models\Institution;
use App\Models\Report;
use App\Models\ScoreHistory;
use App\Models\SiteVisit;
use App\Models\Subject;
use App\Models\Support;
use App\Models\User;
use App\Services\Concerns\BuildsDailyChart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\DeletedModels\Models\DeletedModel;

class SuperAdminDashboardService
{
    use BuildsDailyChart;

    /**
     * System-wide XP earned over the last 7 days.
     *
     * @return list<array<string, int|string>>
     */
    public function getPerformanceChart(): array
    {
        $raw = ScoreHistory::dailyPointsSince(now()->subDays(6)->startOfDay());

        return $this->dailyChart($raw, 'points');
    }

    /**
     * Public site visits over the last 7 days.
     *
     * @return list<array<string, int|string>>
     */
    public function getSiteVisitsChart(): array
    {
        $raw = SiteVisit::dailyCountsSince(now()->subDays(6)->startOfDay());

        return $this->dailyChart($raw, 'visits');
    }

    /**
     * Get system metrics.
     */
    public function getMetrics(): array
    {
        return [
            'total_users' => User::query()->count(),
            'total_institutions' => Institution::query()->count(),
            'total_subjects' => Subject::query()->count(),
            'total_deleted' => DeletedModel::query()->count(),
            'active_students' => User::query()->where('role', UserRole::STUDENT)->count(),
            'total_xp' => (int) User::query()->sum('points'),
            'active_subjects' => Subject::query()->count(),
            'users_by_role' => User::query()
                ->selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->get()
                ->mapWithKeys(fn ($item) => [
                    $item->role->value => $item->getAttribute('count'),
                ]),
        ];
    }

    /**
     * Get all institutions.
     */
    public function getInstitutions(): array
    {
        return InstitutionResource::collection(
            Institution::query()
                ->withCount(['users', 'subjects'])
                ->get(),
        )->resolve();
    }

    /**
     * Get all users (except super admins).
     */
    public function getUsers(): array
    {
        return UserResource::collection(
            User::query()
                ->where('role', '!=', UserRole::SUPER_ADMIN)
                ->with(['institution', 'institutions', 'enrolledClassrooms:id'])
                ->get(),
        )->resolve();
    }

    /**
     * Get all subjects.
     */
    public function getSubjects(): array
    {
        return SubjectResource::collection(
            Subject::with('institution')->get(),
        )->resolve();
    }

    /**
     * Get recently deleted models.
     */
    public function getDeletedModels(): array
    {
        return DeletedModel::query()
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * Get reports requested by specific user.
     */
    public function getReports(int $userId): array
    {
        return Report::query()
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * Get support requests.
     */
    public function getSupports(): array
    {
        return Support::with('user.institution')
            ->latest()
            ->get()
            ->map(fn ($support) => [
                'id' => $support->id,
                'user_name' => $support->user->name,
                'user_email' => $support->user->email,
                'user_role' => $support->user->role->value,
                'institution_name' => $support->user->institution ? $support->user->institution->name : 'Nenhuma',
                'subject' => $support->subject,
                'message' => $support->message,
                'status' => $support->status,
                'reply' => $support->reply,
                'replied_at' => $support->replied_at ? $support->replied_at->toIso8601String() : null,
                'created_at' => $support->created_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Get site visits with decrypted IP addresses.
     */
    public function getSiteVisits(): array
    {
        return SiteVisit::orderByDesc('visited_at')
            ->take(100)
            ->get()
            ->map(fn ($visit) => [
                'id' => $visit->id,
                'ip_address' => $visit->ip_address,
                'user_agent' => $visit->user_agent,
                'visited_at' => $visit->visited_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Get failed jobs formatting.
     */
    public function getFailedJobs(): array
    {
        return DB::table('failed_jobs')
            ->latest('failed_at')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $displayName = 'Unknown Job';

                if ($payload && isset($payload['data']['commandName'])) {
                    $displayName = basename(str_replace('\\', '/', $payload['data']['commandName']));
                } elseif ($payload && isset($payload['displayName'])) {
                    $displayName = basename(str_replace('\\', '/', $payload['displayName']));
                }

                $exception = $job->exception;
                $shortException = mb_strlen($exception) > 150 ? mb_substr($exception, 0, 150) . '...' : $exception;

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'display_name' => $displayName,
                    'exception' => $exception,
                    'short_exception' => $shortException,
                    'failed_at' => Carbon::parse($job->failed_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }
}
