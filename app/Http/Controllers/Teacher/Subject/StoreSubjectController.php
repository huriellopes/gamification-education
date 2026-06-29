<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Actions\Teacher\CreateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Subject\StoreSubjectRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreSubjectController extends Controller
{
    /**
     * Cadastra uma nova matéria e associa ao professor logado.
     */
    public function __invoke(StoreSubjectRequest $request, CreateSubjectAction $createSubject): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $createSubject($request->validated(), $user);

        return to_route('teacher.subjects.index')->with('success', 'Matéria criada com sucesso e associada ao seu perfil!');
    }
}
