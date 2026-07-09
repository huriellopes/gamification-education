<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Jobs\SendPasswordResetByManagerJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPasswordByManagerAction
{
    /**
     * Redefine a senha do usuário-alvo para uma senha temporária aleatória,
     * exige a troca no próximo login e enfileira o e-mail de aviso. O papel do
     * gestor (super admin / admin) é informado ao usuário no e-mail.
     *
     * Retorna a senha temporária gerada (útil para testes/feedback).
     */
    public function execute(User $target, User $manager): string
    {
        $temporaryPassword = Str::random(12);

        // forceFill + save mantém a semântica do fluxo de reset self-service:
        // ciclar o remember_token invalida sessões "lembradas" com a senha antiga.
        $target->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        dispatch(new SendPasswordResetByManagerJob(
            $target,
            $temporaryPassword,
            $manager->role->label(),
        ));

        return $temporaryPassword;
    }
}
