<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    /**
     * Exibe o dashboard do professor com suas matérias associadas.
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $subjects = $user->subjects()
            ->withCount(['studyMaterials', 'tests'])
            ->get();

        return Inertia::render('Teacher/Dashboard', [
            'subjects' => $subjects,
        ]);
    }
}
