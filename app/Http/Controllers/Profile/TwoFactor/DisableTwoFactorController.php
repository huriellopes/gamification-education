<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile\TwoFactor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DisableTwoFactorController extends Controller
{
    /**
     * Desativa o 2FA, removendo a chave secreta e os códigos de recuperação.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Autenticação de dois fatores desativada.');
    }
}
