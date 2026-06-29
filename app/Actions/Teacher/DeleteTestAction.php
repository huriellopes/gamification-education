<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Test;

class DeleteTestAction
{
    /**
     * Exclui um teste.
     */
    public function __invoke(Test $test): void
    {
        $test->delete();
    }
}
