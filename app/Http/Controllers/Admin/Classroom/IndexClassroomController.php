<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ClassroomResource;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IndexClassroomController extends Controller
{
    /**
     * Lista as turmas da instituição do administrador.
     */
    public function __invoke(): Response
    {
        Gate::authorize('viewAny', Classroom::class);

        /** @var User $user */
        $user = auth()->user();
        $institutionId = (int) $user->institution_id;

        $classrooms = Classroom::query()
            ->forInstitution($institutionId)
            ->with(['teacher:id,name', 'subjects:id,name,classroom_id'])
            ->withCount('subjects')
            ->latest()
            ->get();

        return Inertia::render('Admin/Classrooms/Index', [
            'classrooms' => ClassroomResource::collection($classrooms)->resolve(),
            'teachers' => User::query()
                ->teachers()
                ->forInstitution($institutionId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'subjects' => Subject::query()
                ->forInstitution($institutionId)
                ->orderBy('name')
                ->get(['id', 'name', 'classroom_id']),
        ]);
    }
}
