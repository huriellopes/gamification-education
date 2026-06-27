<?php

namespace App\Actions;

use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Services\MaterialGenerationService;

class GenerateStudyMaterialAction
{
    protected MaterialGenerationService $generationService;

    public function __construct(MaterialGenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    /**
     * Gera um material de estudo para uma matéria específica baseado em um tema.
     */
    public function execute(Subject $subject, string $theme): StudyMaterial
    {
        // Gera o conteúdo estruturado do material a partir do tema
        $generatedData = $this->generationService->generateMaterialData($theme);

        return StudyMaterial::create([
            'subject_id' => $subject->id,
            'title' => $generatedData['title'],
            'content' => $generatedData['content'],
            'points_reward' => 15, // Padrão de 15 pontos por leitura de material
        ]);
    }
}
