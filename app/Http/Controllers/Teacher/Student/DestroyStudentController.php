<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class DestroyStudentController extends Controller
{
    /**
     * Exclui um estudante da instituição do professor.
     */
    public function __invoke(User $student): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if((int) $student->institution_id !== (int) $user->institution_id || !$student->isStudent(), 403);

        $student->delete();

        return redirect()->back()->with('success', 'Estudante excluído com sucesso!');
    }
}
