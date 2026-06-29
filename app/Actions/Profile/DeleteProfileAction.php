<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

class DeleteProfileAction
{
    /**
     * Remove a conta do usuário informado.
     */
    public function __invoke(User $user): void
    {
        $user->delete();
    }
}
