<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Subject;

class UpdateSubjectAction
{
    /**
     * Atualiza os dados de uma matéria.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(Subject $subject, array $attributes): Subject
    {
        unset($attributes['institution_id']);

        $subject->update($attributes);

        return $subject;
    }
}
