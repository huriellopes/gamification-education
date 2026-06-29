<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Subject;

class CreateSubjectAction
{
    /**
     * Cria uma matéria forçando o vínculo com a instituição do administrador.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, int $institutionId): Subject
    {
        $attributes['institution_id'] = $institutionId;

        return Subject::create($attributes);
    }
}
