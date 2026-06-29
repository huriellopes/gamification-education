<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Actions\Teacher\UpdateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateSubjectController extends Controller
{
    /**
     * Atualiza uma matéria associada ao professor.
     */
    public function __invoke(UpdateSubjectRequest $request, Subject $subject, UpdateSubjectAction $updateSubject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $updateSubject($subject, $request->validated());

        return redirect()->back()->with('success', 'Matéria atualizada com sucesso!');
    }
}
