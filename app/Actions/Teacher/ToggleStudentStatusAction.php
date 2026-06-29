<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Enums\GeneralStatus;
use App\Models\User;

class ToggleStudentStatusAction
{
    /**
     * Alterna o status (ativo/inativo) de um estudante e retorna o novo status.
     */
    public function __invoke(User $student): GeneralStatus
    {
        $newStatus = $student->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $student->update([
            'is_active' => $newStatus,
        ]);

        return $newStatus;
    }
}
