<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Content\GenerateContentRequest;
use App\Jobs\GenerateContentJob;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class GenerateContentController extends Controller
{
    /**
     * Gera materiais didáticos e atividades baseados em um tema.
     */
    public function __invoke(GenerateContentRequest $request, Subject $subject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $theme = (string) $request->validated('theme');

        dispatch(new GenerateContentJob($subject, $theme));

        return to_route('teacher.subjects.show', $subject)
            ->with('success', 'Geração de conteúdo didático e avaliação iniciada em segundo plano!');
    }
}
