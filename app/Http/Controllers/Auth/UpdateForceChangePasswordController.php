<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\EvictOtherSessionsAction;
use App\Actions\Auth\ForceChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForceChangePasswordRequest;
use Illuminate\Http\RedirectResponse;

class UpdateForceChangePasswordController extends Controller
{
    /**
     * Processa a alteração de senha obrigatória.
     */
    public function __invoke(
        ForceChangePasswordRequest $request,
        ForceChangePasswordAction $action,
        EvictOtherSessionsAction $evictOtherSessions,
    ): RedirectResponse {
        $validated = $request->validated();

        $user = $request->user();

        if ($user) {
            $action->execute($user, $validated['password']);

            // Idem: a senha temporária/antiga não deve manter outras sessões vivas.
            $evictOtherSessions->execute($user, $request->session()->getId());
        }

        return to_route('dashboard')->with('success', 'Sua senha foi alterada com sucesso!');
    }
}
