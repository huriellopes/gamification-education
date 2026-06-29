<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\StudyMaterial;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyStudyMaterialController extends Controller
{
    /**
     * Exclui um material de estudo.
     */
    public function __invoke(StudyMaterial $material): RedirectResponse
    {
        $subject = $material->subject;
        Gate::authorize('manageContent', $subject);

        $material->delete();

        return redirect()->back()->with('success', 'Material de estudo excluído com sucesso!');
    }
}
