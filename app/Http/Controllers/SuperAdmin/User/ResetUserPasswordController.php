<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Actions\Auth\ResetUserPasswordByManagerAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResetUserPasswordController extends Controller
{
    /**
     * Redefine a senha de um usuário para uma senha temporária e avisa por e-mail.
     */
    public function __invoke(Request $request, User $user, ResetUserPasswordByManagerAction $resetPassword): RedirectResponse
    {
        /** @var User $manager */
        $manager = $request->user();

        if (!$manager->can('resetPassword', $user)) {
            abort(403, 'Acesso não autorizado para redefinir a senha deste usuário.');
        }

        $resetPassword->execute($user, $manager);

        return back()->with('success', "A senha de {$user->name} foi redefinida e um e-mail foi enviado com as instruções.");
    }
}
