<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Actions\Admin\DeleteInstitutionUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class DestroyInstitutionUserController extends Controller
{
    /**
     * Exclui um membro da instituição.
     */
    public function __invoke(User $user, DeleteInstitutionUserAction $deleteUser): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if($user->institution_id !== $admin->institution_id || $user->isSuperAdmin(), 403);

        $deleteUser($user);

        return redirect()->back()->with('success', 'Membro enviado para a lixeira com sucesso!');
    }
}
