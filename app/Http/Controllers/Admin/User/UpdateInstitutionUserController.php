<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateInstitutionUserController extends Controller
{
    /**
     * Atualiza um membro (professor ou estudante) da instituição.
     */
    public function __invoke(UserData $data, User $user): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if((int) $user->institution_id !== (int) $admin->institution_id || $user->isSuperAdmin(), 403);

        $attributes = $data->toArray();
        unset($attributes['institution_id']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $user->update($attributes);

        $roleText = $user->isTeacher() ? 'Professor' : 'Estudante';

        return redirect()->back()->with('success', "{$roleText} atualizado com sucesso!");
    }
}
