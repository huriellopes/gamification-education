<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\User;

class DeleteStudentAction
{
    /**
     * Exclui um estudante.
     */
    public function __invoke(User $student): void
    {
        $student->delete();
    }
}
