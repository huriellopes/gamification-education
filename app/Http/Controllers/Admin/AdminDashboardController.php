<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Report;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Exibe o dashboard do administrador da instituição.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $performanceChartRaw = \App\Models\ScoreHistory::whereHas('user', function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId);
            })
            ->selectRaw('DATE(created_at) as date, SUM(points) as total_points')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($item) => [\Carbon\Carbon::parse($item->date)->format('d/m') => (int) $item->total_points])
            ->all();

        $performanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStr = now()->subDays($i)->format('d/m');
            $performanceChart[] = [
                'day' => $dayStr,
                'points' => $performanceChartRaw[$dayStr] ?? 0,
            ];
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_teachers' => User::where('role', UserRole::TEACHER->value)->where('institution_id', $institutionId)->count(),
                'total_subjects' => Subject::where('institution_id', $institutionId)->count(),
                'total_students' => User::where('role', UserRole::STUDENT->value)->where('institution_id', $institutionId)->count(),
            ],
            'performanceChart' => $performanceChart,
            'students' => UserResource::collection(
                User::where('role', UserRole::STUDENT->value)
                    ->where('institution_id', $institutionId)
                    ->orderBy('points', 'desc')
                    ->get(),
            ),
            'teachers' => UserResource::collection(
                User::where('role', UserRole::TEACHER->value)
                    ->where('institution_id', $institutionId)
                    ->get(),
            ),
            'reports' => Report::where('user_id', $user->id)->latest()->get(),
        ]);
    }
}
