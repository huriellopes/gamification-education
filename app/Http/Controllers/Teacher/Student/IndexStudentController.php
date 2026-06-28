<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexStudentController extends Controller
{
    /**
     * Lista todos os estudantes da instituição do professor.
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $students = User::where('role', 'student')
            ->where('institution_id', $institutionId)
            ->get();

        return Inertia::render('Teacher/Students/Index', [
            'students' => UserResource::collection($students),
        ]);
    }
}
