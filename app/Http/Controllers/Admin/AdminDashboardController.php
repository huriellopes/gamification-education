<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Exibe o dashboard do administrador da instituição.
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_teachers' => User::where('role', 'teacher')->where('institution_id', $institutionId)->count(),
                'total_subjects' => Subject::where('institution_id', $institutionId)->count(),
                'total_students' => User::where('role', 'student')->where('institution_id', $institutionId)->count(),
            ],
            'students' => User::where('role', 'student')
                ->where('institution_id', $institutionId)
                ->orderBy('points', 'desc')
                ->get(),
            'teachers' => User::where('role', 'teacher')
                ->where('institution_id', $institutionId)
                ->get(),
        ]);
    }
}
