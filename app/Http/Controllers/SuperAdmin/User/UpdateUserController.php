<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Actions\SuperAdmin\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    public function __invoke(UpdateUserRequest $request, User $user, UpdateUserAction $updateUser): RedirectResponse
    {
        $updateUser($user, $request->validated());

        return back()->with('success', 'Usuário atualizado com sucesso!');
    }
}
