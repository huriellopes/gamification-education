<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        if ($user->isInstitutionAdmin() && $user->institution_id) {
            $user->institutions()->syncWithoutDetaching([$user->institution_id]);
        } elseif (!$user->isInstitutionAdmin()) {
            $user->institutions()->detach();
        }
    }
}
