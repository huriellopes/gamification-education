<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Actions\Teacher\ToggleStudentStatusAction;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ToggleStudentStatusController extends Controller
{
    /**
     * Ativa/Desativa um estudante.
     */
    public function __invoke(User $student, ToggleStudentStatusAction $toggleStatus): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if((int) $student->institution_id !== (int) $user->institution_id || !$student->isStudent(), 403);

        $newStatus = $toggleStatus($student);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativado' : 'desativado';

        return redirect()->back()->with('success', "Estudante {$statusText} com sucesso!");
    }
}
