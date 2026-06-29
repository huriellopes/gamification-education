<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\StudyMaterial;

use App\Data\Teacher\StudyMaterialData;
use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StoreStudyMaterialController extends Controller
{
    /**
     * Cadastra um novo material de estudo para a matéria.
     */
    public function __invoke(StudyMaterialData $data, Subject $subject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        $attributes['subject_id'] = $subject->id;

        StudyMaterial::create($attributes);

        return redirect()->back()->with('success', 'Material de estudo cadastrado com sucesso!');
    }
}
