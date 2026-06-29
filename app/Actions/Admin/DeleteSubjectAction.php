<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Subject;

class DeleteSubjectAction
{
    /**
     * Exclui (envia para a lixeira) uma matéria.
     */
    public function __invoke(Subject $subject): void
    {
        $subject->delete();
    }
}
