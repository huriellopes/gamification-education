<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        SiteVisit::recordVisit(request()->ip() ?? '127.0.0.1', request()->userAgent());

        $totalStudents = User::activeStudentsCount();
        $totalSubjects = Subject::activeCount();
        $totalXp = User::studentsTotalXp();

        $formattedXp = $totalXp >= 1000 ? round($totalXp / 1000, 1) . 'k' : (string) $totalXp;

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'stats' => [
                'students' => $totalStudents,
                'subjects' => $totalSubjects,
                'xp' => $formattedXp,
            ],
        ]);
    }
}
