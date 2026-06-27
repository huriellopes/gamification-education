<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateStudyMaterialAction;
use App\Actions\GenerateTestForSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateContentRequest;
use App\Models\Subject;

class ContentGenerationController extends Controller
{
    protected GenerateStudyMaterialAction $generateMaterialAction;

    protected GenerateTestForSubjectAction $generateTestAction;

    public function __construct(
        GenerateStudyMaterialAction $generateMaterialAction,
        GenerateTestForSubjectAction $generateTestAction
    ) {
        $this->generateMaterialAction = $generateMaterialAction;
        $this->generateTestAction = $generateTestAction;
    }

    /**
     * Dispara a geração de materiais e testes para uma matéria.
     */
    public function generate(GenerateContentRequest $request, Subject $subject)
    {
        $theme = $request->input('theme');

        // Gera o material de estudo
        $material = $this->generateMaterialAction->execute($subject, $theme);

        // Gera o teste correspondente
        $test = $this->generateTestAction->execute($subject, $theme);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', "Conteúdo gerado com sucesso para o tema '{$theme}'! Material '{$material->title}' e Teste '{$test->title}' criados.");
    }
}
