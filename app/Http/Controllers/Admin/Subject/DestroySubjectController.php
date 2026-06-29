<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Actions\Admin\DeleteSubjectAction;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroySubjectController extends Controller
{
    /**
     * Exclui uma matéria da instituição do administrador.
     */
    public function __invoke(Subject $subject, DeleteSubjectAction $deleteSubject): RedirectResponse
    {
        Gate::authorize('delete', $subject);

        $deleteSubject($subject);

        return redirect()->back()->with('success', 'Matéria enviada para a lixeira com sucesso!');
    }
}
