<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Models\Classroom;
use App\Models\User;

class ApproveClassroomAction
{
    /**
     * Aprova uma turma pendente: registra quem/quando aprovou e a torna ativa.
     * Idempotente — reaprovar não altera o aprovador original.
     */
    public function __invoke(Classroom $classroom, User $approver): Classroom
    {
        if ($classroom->isApproved()) {
            return $classroom;
        }

        $classroom->forceFill([
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'is_active' => GeneralStatus::ACTIVE,
        ])->save();

        return $classroom;
    }
}
