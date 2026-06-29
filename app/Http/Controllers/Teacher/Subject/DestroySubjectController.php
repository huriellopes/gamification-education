<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Actions\Teacher\DeleteSubjectAction;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroySubjectController extends Controller
{
    /**
     * Exclui uma matéria associada ao professor.
     */
    public function __invoke(Subject $subject, DeleteSubjectAction $deleteSubject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $deleteSubject($subject);

        return redirect()->route('teacher.dashboard')->with('success', 'Matéria enviada para a lixeira com sucesso!');
    }
}
