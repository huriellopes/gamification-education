<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institution;
use App\Models\Subject;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $students = User::with('institution')
            ->where('role', 'student')
            ->orderBy('points', 'desc')
            ->get();

        $stats = [
            'total_students' => $students->count(),
            'total_institutions' => Institution::count(),
            'total_subjects' => Subject::count(),
            'total_points_distributed' => $students->sum('points'),
        ];

        return Inertia::render('Admin/Dashboard', [
            'students' => $students,
            'stats' => $stats,
        ]);
    }
}
