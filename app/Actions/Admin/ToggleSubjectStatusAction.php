<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Models\Subject;

class ToggleSubjectStatusAction
{
    /**
     * Alterna o status de ativação da matéria e devolve o novo status.
     */
    public function __invoke(Subject $subject): GeneralStatus
    {
        $newStatus = $subject->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $subject->update([
            'is_active' => $newStatus,
        ]);

        return $newStatus;
    }
}
