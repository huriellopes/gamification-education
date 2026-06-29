<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\ClassroomResource;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexClassroomController extends Controller
{
    /**
     * Exibe a lista de turmas para o Super Admin.
     */
    public function __invoke(): Response
    {
        $classrooms = Classroom::query()
            ->with(['teacher:id,name', 'institution:id,name', 'subjects:id,name,classroom_id'])
            ->withCount('subjects')
            ->latest()
            ->get();

        return Inertia::render('SuperAdmin/Classrooms', [
            'classrooms' => ClassroomResource::collection($classrooms)->resolve(),
            'institutions' => Institution::query()->orderBy('name')->get(['id', 'name']),
            'teachers' => User::query()
                ->teachers()
                ->orderBy('name')
                ->get(['id', 'name', 'institution_id']),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name', 'institution_id', 'classroom_id']),
        ]);
    }
}
