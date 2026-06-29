<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Classroom;
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

        // Apenas alunos matriculados em turmas do próprio professor logado.
        $students = User::query()
            ->students()
            ->whereHas('enrolledClassrooms', fn ($query) => $query->where('teacher_id', $user->id))
            ->with('enrolledClassrooms:id,name')
            ->get();

        return Inertia::render('Teacher/Students/Index', [
            'students' => UserResource::collection($students),
            'classrooms' => Classroom::query()
                ->where('teacher_id', $user->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
