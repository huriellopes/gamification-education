<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Study;

use App\Http\Controllers\Controller;
use App\Http\Resources\Student\SubjectResource;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentStudyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowStudyController extends Controller
{
    /**
     * Exibe a trilha de aprendizado de uma matéria (materiais e testes).
     */
    public function __invoke(Subject $subject, StudentStudyService $study): Response
    {
        Gate::authorize('view', $subject);

        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('Student/Subjects/Show', [
            'subject' => (new SubjectResource($subject))->resolve(),
            'materials' => $study->getMaterialsProgress($subject, $user),
            'tests' => $study->getTestsProgress($subject, $user),
        ]);
    }
}
