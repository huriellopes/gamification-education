<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Classroom;

use App\Actions\Teacher\EnrollStudentsInClassroomAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Classroom\EnrollStudentsRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class EnrollStudentsController extends Controller
{
    /**
     * Matricula alunos (filtrados pelo professor) numa turma que ele leciona.
     */
    public function __invoke(
        EnrollStudentsRequest $request,
        Classroom $classroom,
        EnrollStudentsInClassroomAction $enrollStudents,
    ): RedirectResponse {
        /** @var User $teacher */
        $teacher = auth()->user();

        abort_unless($classroom->teacher_id === $teacher->id, 403);

        $count = $enrollStudents($classroom, $request->validated()['student_ids']);

        return back()->with('success', "{$count} aluno(s) adicionado(s) à turma!");
    }
}
