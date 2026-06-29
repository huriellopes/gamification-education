<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Actions\Admin\AssignTeachersToSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subject\AssignTeachersRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AssignTeachersController extends Controller
{
    /**
     * Associa professores a uma matéria.
     */
    public function __invoke(
        AssignTeachersRequest $request,
        Subject $subject,
        AssignTeachersToSubjectAction $assignTeachers,
    ): RedirectResponse {
        Gate::authorize('update', $subject);

        /** @var array<int, int> $teacherIds */
        $teacherIds = $request->validated('teacher_ids');

        $assignTeachers($subject, $teacherIds);

        return redirect()->back()->with('success', 'Professores associados com sucesso!');
    }
}
