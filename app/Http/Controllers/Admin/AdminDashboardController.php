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

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_teachers' => User::where('role', UserRole::TEACHER->value)->where('institution_id', $institutionId)->count(),
                'total_subjects' => Subject::where('institution_id', $institutionId)->count(),
                'total_students' => User::where('role', UserRole::STUDENT->value)->where('institution_id', $institutionId)->count(),
            ],
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
