<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Models\Subject;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowSubjectContentController extends Controller
{
    /**
     * Exibe o painel de gerenciamento de conteúdo da matéria para o professor.
     */
    public function __invoke(Subject $subject): Response
    {
        Gate::authorize('manageContent', $subject);

        $subject->load(['studyMaterials', 'tests.questions']);

        return Inertia::render('Teacher/Subjects/Show', [
            'subject' => (new SubjectResource($subject))->resolve(),
        ]);
    }
}
