<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Actions\Admin\UpdateInstitutionUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UpdateInstitutionUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateInstitutionUserController extends Controller
{
    /**
     * Atualiza um membro (professor ou estudante) da instituição.
     */
    public function __invoke(
        UpdateInstitutionUserRequest $request,
        User $user,
        UpdateInstitutionUserAction $updateUser,
    ): RedirectResponse {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if((int) $user->institution_id !== (int) $admin->institution_id || $user->isSuperAdmin(), 403);

        $updateUser($user, $request->validated());

        $roleText = $user->isTeacher() ? 'Professor' : 'Estudante';

        return back()->with('success', "{$roleText} atualizado com sucesso!");
    }
}
