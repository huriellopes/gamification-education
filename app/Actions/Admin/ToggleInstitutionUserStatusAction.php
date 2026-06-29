<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Models\User;

class ToggleInstitutionUserStatusAction
{
    /**
     * Alterna o status de ativação do membro e devolve o novo status.
     */
    public function __invoke(User $user): GeneralStatus
    {
        $newStatus = $user->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $user->update([
            'is_active' => $newStatus,
        ]);

        return $newStatus;
    }
}
