<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Study;

use App\Actions\Student\CompleteStudyMaterialAction;
use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CompleteStudyMaterialController extends Controller
{
    /**
     * Marca o material de estudo como concluído pelo aluno, gerando pontuações.
     */
    public function __invoke(Subject $subject, StudyMaterial $material, CompleteStudyMaterialAction $action): RedirectResponse
    {
        abort_if($material->subject_id !== $subject->id, 404);

        Gate::authorize('complete', $material);

        /** @var User $user */
        $user = Auth::user();
        $success = $action->execute($user, $material);

        if ($success) {
            return to_route('student.subjects.show', $subject)
                ->with('success', "Leitura concluída! Você ganhou +{$material->points_reward} pontos!");
        }

        return to_route('student.subjects.show', $subject)
            ->with('info', 'Você já havia concluído este material anteriormente.');
    }
}
