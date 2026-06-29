<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Classroom;

class DeleteClassroomAction
{
    /**
     * Remove uma turma.
     */
    public function __invoke(Classroom $classroom): void
    {
        $classroom->delete();
    }
}
