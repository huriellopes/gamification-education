<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Models\Classroom;

class ToggleClassroomStatusAction
{
    /**
     * Alterna o status de ativação da turma e devolve o novo status.
     */
    public function __invoke(Classroom $classroom): GeneralStatus
    {
        $newStatus = $classroom->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $classroom->update([
            'is_active' => $newStatus,
        ]);

        return $newStatus;
    }
}
