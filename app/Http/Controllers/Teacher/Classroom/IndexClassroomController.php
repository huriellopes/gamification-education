<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexClassroomController extends Controller
{
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $classrooms = Classroom::query()
            ->where('teacher_id', $user->id)
            ->with(['subjects:id,name,classroom_id,slug', 'students:id'])
            ->withCount(['subjects', 'students'])
            ->latest()
            ->get();

        // Alunos da instituição do professor, disponíveis para matrícula.
        $students = User::query()
            ->students()
            ->forInstitution((int) $user->institution_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Teacher/Classrooms/Index', [
            'classrooms' => ClassroomResource::collection($classrooms)->resolve(),
            'students' => $students,
        ]);
    }
}
