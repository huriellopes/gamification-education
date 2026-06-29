<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Actions\Admin\UpdateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class UpdateSubjectController extends Controller
{
    /**
     * Atualiza uma matéria da instituição do administrador.
     */
    public function __invoke(
        UpdateSubjectRequest $request,
        Subject $subject,
        UpdateSubjectAction $updateSubject,
    ): RedirectResponse {
        $updateSubject($subject, $request->validated());

        return redirect()->back()->with('success', 'Matéria atualizada com sucesso!');
    }
}
