<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Actions\SuperAdmin\User\CreateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\User\StoreUserRequest;
use Illuminate\Http\RedirectResponse;

class StoreUserController extends Controller
{
    public function __invoke(StoreUserRequest $request, CreateUserAction $createUser): RedirectResponse
    {
        $createUser($request->validated());

        return back()->with('success', 'Usuário criado com sucesso!');
    }
}
