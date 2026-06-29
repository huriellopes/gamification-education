<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\StudyMaterial;

use App\Actions\Teacher\CreateStudyMaterialAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StudyMaterial\StoreStudyMaterialRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StoreStudyMaterialController extends Controller
{
    /**
     * Cadastra um novo material de estudo para a matéria.
     */
    public function __invoke(StoreStudyMaterialRequest $request, Subject $subject, CreateStudyMaterialAction $createStudyMaterial): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $createStudyMaterial($request->validated(), $subject);

        return back()->with('success', 'Material de estudo cadastrado com sucesso!');
    }
}
