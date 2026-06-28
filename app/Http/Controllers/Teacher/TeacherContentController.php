<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Data\Teacher\GenerateContentData;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Jobs\GenerateContentJob;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeacherContentController extends Controller
{
    /**
     * Exibe o painel de gerenciamento de conteúdo da matéria para o professor.
     */
    public function show(Subject $subject): Response
    {
        Gate::authorize('manageContent', $subject);

        $subject->load(['studyMaterials', 'tests.questions']);

        return Inertia::render('Teacher/Subjects/Show', [
            'subject' => new SubjectResource($subject),
        ]);
    }

    /**
     * Gera materiais didáticos e atividades baseados em um tema.
     */
    public function generate(
        Subject $subject,
        GenerateContentData $data,
    ): RedirectResponse {
        Gate::authorize('manageContent', $subject);

        $theme = $data->theme;

        GenerateContentJob::dispatch($subject, $theme);

        return redirect()->route('teacher.subjects.show', $subject)
            ->with('success', 'Geração de conteúdo didático e avaliação iniciada em segundo plano!');
    }
}
