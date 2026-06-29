<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\StudyMaterial;
use App\Models\Subject;

class CreateStudyMaterialAction
{
    /**
     * Cria um material de estudo vinculado à matéria.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, Subject $subject): StudyMaterial
    {
        $attributes['subject_id'] = $subject->id;

        return StudyMaterial::create($attributes);
    }
}
