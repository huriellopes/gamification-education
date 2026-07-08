<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile\TwoFactor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegenerateRecoveryCodesController extends Controller
{
    /**
     * Gera um novo conjunto de códigos de recuperação.
     */
    public function __invoke(Request $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($user->two_factor_secret === null, 404);

        // Gerar novos códigos invalida os antigos — reconfirma a senha (F2).
        $request->validate(
            ['current_password' => ['required', 'current_password']],
            [],
            ['current_password' => __('profile.two_factor.current_password_label')],
        );

        $user->forceFill([
            'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
        ])->save();

        return back()->with('success', 'Novos códigos de recuperação gerados.');
    }
}
