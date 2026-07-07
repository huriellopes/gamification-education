<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile\TwoFactor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnableTwoFactorController extends Controller
{
    /**
     * Inicia a configuração do 2FA: gera a chave secreta e os códigos de
     * recuperação (ainda não confirmado — o usuário precisa validar um código).
     */
    public function __invoke(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => $service->generateSecretKey(),
            'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back();
    }
}
