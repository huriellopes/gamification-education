<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ForceChangePasswordAction
{
    /**
     * Define uma nova senha para o usuário e remove a obrigatoriedade de troca.
     */
    public function execute(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->must_change_password = false;
        $user->save();
    }
}
