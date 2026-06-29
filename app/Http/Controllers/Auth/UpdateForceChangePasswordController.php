<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\ForceChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForceChangePasswordRequest;
use Illuminate\Http\RedirectResponse;

class UpdateForceChangePasswordController extends Controller
{
    /**
     * Processa a alteração de senha obrigatória.
     */
    public function __invoke(ForceChangePasswordRequest $request, ForceChangePasswordAction $action): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        if ($user) {
            $action->execute($user, $validated['password']);
        }

        return redirect()->route('dashboard')->with('success', 'Sua senha foi alterada com sucesso!');
    }
}
