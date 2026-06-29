<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Subject;

use App\Enums\GeneralStatus;
use App\Models\Subject;

class CreateSubjectAction
{
    /**
     * Cria uma matéria (SuperAdmin define livremente a instituição de destino).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): Subject
    {
        $attributes['is_active'] ??= GeneralStatus::ACTIVE;

        return Subject::create($attributes);
    }
}
