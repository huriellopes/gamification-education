<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Study;

use App\Http\Controllers\Controller;
use App\Http\Resources\Student\StudyMaterialResource;
use App\Http\Resources\Student\SubjectResource;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowStudyMaterialController extends Controller
{
    /**
     * Exibe os detalhes de um material de estudo para leitura.
     */
    public function __invoke(Subject $subject, StudyMaterial $material): Response
    {
        // Garante que o material pertence à matéria
        abort_if($material->subject_id !== $subject->id, 404);

        Gate::authorize('view', $material);

        /** @var User $user */
        $user = Auth::user();
        $completed = $user->hasCompletedMaterial($material->id);

        return Inertia::render('Student/Materials/Show', [
            'subject' => (new SubjectResource($subject))->resolve(),
            'material' => (new StudyMaterialResource($material))->resolve(),
            'completed' => $completed,
        ]);
    }
}
