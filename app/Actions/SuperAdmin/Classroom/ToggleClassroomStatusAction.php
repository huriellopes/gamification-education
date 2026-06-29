<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Classroom;

use App\Enums\GeneralStatus;
use App\Models\Classroom;

class ToggleClassroomStatusAction
{
    /**
     * Alterna o status (ativo/inativo) de uma turma.
     */
    public function __invoke(Classroom $classroom): Classroom
    {
        $classroom->update([
            'is_active' => $classroom->is_active === GeneralStatus::ACTIVE
                ? GeneralStatus::INACTIVE
                : GeneralStatus::ACTIVE,
        ]);

        return $classroom;
    }
}
