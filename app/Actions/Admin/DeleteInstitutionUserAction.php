<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;

class DeleteInstitutionUserAction
{
    /**
     * Exclui (envia para a lixeira) um membro da instituição.
     */
    public function __invoke(User $user): void
    {
        $user->delete();
    }
}
