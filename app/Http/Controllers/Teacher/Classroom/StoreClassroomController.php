<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Classroom;

use App\Actions\Teacher\CreateClassroomAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Classroom\StoreClassroomRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreClassroomController extends Controller
{
    /**
     * Cria uma turma do professor (pendente de aprovação do admin).
     */
    public function __invoke(StoreClassroomRequest $request, CreateClassroomAction $createClassroom): RedirectResponse
    {
        /** @var User $teacher */
        $teacher = $request->user();

        $createClassroom($request->validated(), $teacher);

        return back()->with('success', __('classrooms.created_pending'));
    }
}
