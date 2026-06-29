<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Actions\Admin\CreateInstitutionUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreInstitutionUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreInstitutionUserController extends Controller
{
    /**
     * Cadastra um novo membro (professor ou estudante) na instituição.
     */
    public function __invoke(
        StoreInstitutionUserRequest $request,
        CreateInstitutionUserAction $createUser,
    ): RedirectResponse {
        /** @var User $admin */
        $admin = auth()->user();

        $attributes = $request->validated();

        $createUser($attributes, (int) $admin->institution_id);

        $roleText = ($attributes['role'] ?? null) === 'teacher' ? 'Professor' : 'Estudante';

        return redirect()->back()->with('success', "{$roleText} cadastrado com sucesso!");
    }
}
