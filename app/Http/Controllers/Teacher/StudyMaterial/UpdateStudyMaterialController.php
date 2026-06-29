<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\StudyMaterial;

use App\Actions\Teacher\UpdateStudyMaterialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StudyMaterial\UpdateStudyMaterialRequest;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateStudyMaterialController extends Controller
{
    /**
     * Atualiza um material de estudo.
     */
    public function __invoke(UpdateStudyMaterialRequest $request, StudyMaterial $material, UpdateStudyMaterialAction $updateStudyMaterial): RedirectResponse
    {
        $subject = $material->subject;
        Gate::authorize('manageContent', $subject);

        $updateStudyMaterial($material, $request->validated());

        return back()->with('success', 'Material de estudo atualizado com sucesso!');
    }
}
