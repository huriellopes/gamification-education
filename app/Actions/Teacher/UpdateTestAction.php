<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Test;

class UpdateTestAction
{
    /**
     * Atualiza um teste.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(Test $test, array $attributes): Test
    {
        unset($attributes['subject_id']);

        $test->update($attributes);

        return $test;
    }
}
