<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\GenerateStudyMaterialAction;
use App\Actions\GenerateTestForSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateContentRequest;
use App\Models\Subject;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeacherContentController extends Controller
{
    /**
     * Exibe o painel de gerenciamento de conteúdo da matéria para o professor.
     */
    public function show(Subject $subject)
    {
        Gate::authorize('manageContent', $subject);

        $subject->load(['studyMaterials', 'tests.questions']);

        return Inertia::render('Teacher/Subjects/Show', [
            'subject' => $subject,
        ]);
    }

    /**
     * Gera materiais didáticos e atividades baseados em um tema.
     */
    public function generate(
        Subject $subject,
        GenerateContentRequest $request,
        GenerateStudyMaterialAction $generateMaterial,
        GenerateTestForSubjectAction $generateTest
    ) {
        Gate::authorize('manageContent', $subject);

        $theme = $request->input('theme');

        // Dispara as actions de criação de conteúdo didático e quiz
        $generateMaterial->execute($subject, $theme);
        $generateTest->execute($subject, $theme);

        return redirect()->route('teacher.subjects.show', $subject)
            ->with('success', 'Conteúdo didático e avaliação gerados com sucesso!');
    }
}
