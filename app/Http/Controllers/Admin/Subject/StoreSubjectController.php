<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Actions\Admin\CreateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subject\StoreSubjectRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreSubjectController extends Controller
{
    /**
     * Cadastra uma nova matéria na instituição do administrador.
     */
    public function __invoke(StoreSubjectRequest $request, CreateSubjectAction $createSubject): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $createSubject($request->validated(), (int) $user->institution_id);

        return redirect()->back()->with('success', 'Matéria criada com sucesso!');
    }
}
