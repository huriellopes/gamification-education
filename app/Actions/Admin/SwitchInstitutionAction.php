<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Institution;
use App\Models\User;

class SwitchInstitutionAction
{
    /**
     * Define a instituição de contexto ativa do administrador.
     */
    public function __invoke(User $user, Institution $institution): void
    {
        $user->institution_id = $institution->id;
        $user->save();
    }
}
