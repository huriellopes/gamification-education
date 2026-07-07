<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile\TwoFactor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ConfirmTwoFactorRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorController extends Controller
{
    /**
     * Confirma a ativação do 2FA validando um código do app autenticador.
     */
    public function __invoke(ConfirmTwoFactorRequest $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $code = preg_replace('/\s+/', '', (string) $request->validated('code'));

        if ($user->two_factor_secret === null || !$service->verify($user->two_factor_secret, (string) $code)) {
            throw ValidationException::withMessages([
                'code' => 'O código informado é inválido. Tente novamente.',
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('success', 'Autenticação de dois fatores ativada com sucesso!');
    }
}
