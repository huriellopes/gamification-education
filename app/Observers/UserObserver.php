<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     *
     * Admins e professores podem pertencer a várias instituições (pivot):
     * garantimos que a instituição principal esteja vinculada, SEM remover as
     * demais. Apenas alunos (instituição única) são desvinculados do pivot.
     */
    public function saved(User $user): void
    {
        if (($user->isInstitutionAdmin() || $user->isTeacher()) && $user->institution_id) {
            $user->institutions()->syncWithoutDetaching([$user->institution_id]);
        } elseif ($user->isStudent()) {
            $user->institutions()->detach();
        }
    }
}
