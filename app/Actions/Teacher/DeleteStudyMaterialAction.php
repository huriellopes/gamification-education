<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\StudyMaterial;

class DeleteStudyMaterialAction
{
    /**
     * Exclui um material de estudo.
     */
    public function __invoke(StudyMaterial $material): void
    {
        $material->delete();
    }
}
