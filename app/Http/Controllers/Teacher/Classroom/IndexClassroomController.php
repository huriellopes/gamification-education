<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\Teacher\ClassroomResource;
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
            ->with(['subjects:id,name,classroom_id,slug'])
            ->withCount('subjects')
            ->latest()
            ->get();

        return Inertia::render('Teacher/Classrooms/Index', [
            'classrooms' => ClassroomResource::collection($classrooms)->resolve(),
        ]);
    }
}
