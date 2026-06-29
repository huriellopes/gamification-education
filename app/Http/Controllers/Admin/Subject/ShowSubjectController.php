<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Http\Resources\UserResource;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowSubjectController extends Controller
{
    /**
     * Exibe os detalhes da matéria e permite gerenciar professores.
     */
    public function __invoke(Subject $subject): Response
    {
        Gate::authorize('view', $subject);

        /** @var User $user */
        $user = auth()->user();

        $subject->load(['institution', 'studyMaterials', 'tests.questions', 'teachers']);

        $teachers = User::query()
            ->teachers()
            ->forInstitution((int) $user->institution_id)
            ->get();

        return Inertia::render('Admin/Subjects/Show', [
            'subject' => (new SubjectResource($subject))->resolve(),
            'availableTeachers' => UserResource::collection($teachers)->resolve(),
        ]);
    }
}
