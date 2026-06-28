<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\StudyMaterial;

use App\Data\Teacher\StudyMaterialData;
use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateStudyMaterialController extends Controller
{
    /**
     * Atualiza um material de estudo.
     */
    public function __invoke(StudyMaterialData $data, StudyMaterial $material): RedirectResponse
    {
        $subject = $material->subject;
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        unset($attributes['subject_id']);
        $material->update($attributes);

        return redirect()->back()->with('success', 'Material de estudo atualizado com sucesso!');
    }
}
