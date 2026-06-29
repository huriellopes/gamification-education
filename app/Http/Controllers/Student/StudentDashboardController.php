<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentDashboardController extends Controller
{
    public function __invoke(StudentDashboardService $dashboard): Response
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('Student/Dashboard', [
            'subjects' => $dashboard->getSubjectsProgress($user),
            'scoreHistory' => $dashboard->getRecentScoreHistory($user),
            'leaderboard' => $dashboard->getLeaderboard(),
            'stats' => $dashboard->getQuickStats($user),
        ]);
    }
}
