<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Actions\Teacher\UpdateStudentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Student\UpdateStudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateStudentController extends Controller
{
    /**
     * Atualiza um estudante da instituição do professor.
     */
    public function __invoke(UpdateStudentRequest $request, User $student, UpdateStudentAction $updateStudent): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if((int) $student->institution_id !== (int) $user->institution_id || !$student->isStudent(), 403);

        $updateStudent($student, $request->validated());

        return redirect()->back()->with('success', 'Estudante atualizado com sucesso!');
    }
}
