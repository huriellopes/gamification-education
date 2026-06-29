<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Subject;
use App\Models\Test;

class CreateTestAction
{
    /**
     * Cria um teste vinculado à matéria.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, Subject $subject): Test
    {
        $attributes['subject_id'] = $subject->id;

        return Test::create($attributes);
    }
}
