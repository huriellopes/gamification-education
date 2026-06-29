<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\StudyMaterial;

class UpdateStudyMaterialAction
{
    /**
     * Atualiza um material de estudo.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(StudyMaterial $material, array $attributes): StudyMaterial
    {
        unset($attributes['subject_id']);

        $material->update($attributes);

        return $material;
    }
}
