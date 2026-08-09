<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\EvictOtherSessionsAction;
use App\Actions\Auth\UpdateUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function __invoke(
        UpdatePasswordRequest $request,
        UpdateUserPasswordAction $action,
        EvictOtherSessionsAction $evictOtherSessions,
    ): RedirectResponse {
        $validated = $request->validated();

        $user = $request->user();

        $action->execute($user, $validated['password']);

        // A senha mudou: encerra qualquer outra sessão/dispositivo logado com
        // a senha antiga, mantendo apenas a sessão atual.
        $evictOtherSessions->execute($user, $request->session()->getId());

        return back();
    }
}
