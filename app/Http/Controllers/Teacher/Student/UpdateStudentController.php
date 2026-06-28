<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateStudentController extends Controller
{
    /**
     * Atualiza um estudante da instituição do professor.
     */
    public function __invoke(UserData $data, User $student): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if((int) $student->institution_id !== (int) $user->institution_id || !$student->isStudent(), 403);

        $attributes = $data->toArray();
        unset($attributes['institution_id']);
        unset($attributes['role']); // Garante que a role não é alterada

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $student->update($attributes);

        return redirect()->back()->with('success', 'Estudante atualizado com sucesso!');
    }
}
