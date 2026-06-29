<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Subject;

class UpdateSubjectAction
{
    /**
     * Atualiza uma matéria mantendo o vínculo de instituição imutável.
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
