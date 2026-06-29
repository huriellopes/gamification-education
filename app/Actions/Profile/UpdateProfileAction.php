<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

class UpdateProfileAction
{
    /**
     * Atualiza as informações de perfil do usuário.
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
