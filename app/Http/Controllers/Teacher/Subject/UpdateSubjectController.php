<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Data\SuperAdmin\Subject\SubjectData;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateSubjectController extends Controller
{
    /**
     * Atualiza uma matéria associada ao professor.
     */
    public function __invoke(SubjectData $data, Subject $subject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        unset($attributes['institution_id']);
        $subject->update($attributes);

        return redirect()->back()->with('success', 'Matéria atualizada com sucesso!');
    }
}
