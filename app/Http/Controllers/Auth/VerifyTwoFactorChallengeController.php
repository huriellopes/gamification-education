<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VerifyTwoFactorChallengeController extends Controller
{
    /**
     * Valida o código TOTP (ou um código de recuperação) e finaliza o login.
     */
    public function __invoke(TwoFactorChallengeRequest $request, TwoFactorAuthenticationService $service): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        if (!$userId) {
            return to_route('login');
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if ($user === null || $user->two_factor_secret === null) {
            $request->session()->forget(['login.id', 'login.remember']);

            return to_route('login');
        }

        $code = $request->validated('code');
        $recoveryCode = $request->validated('recovery_code');
        $authenticated = false;

        if (filled($code)) {
            $normalized = (string) preg_replace('/\s+/', '', (string) $code);
            $authenticated = $service->verify($user->two_factor_secret, $normalized);
        } elseif (filled($recoveryCode)) {
            $recoveryCode = mb_trim((string) $recoveryCode);

            if (in_array($recoveryCode, $user->recoveryCodes(), true)) {
                $user->replaceRecoveryCode($recoveryCode);
                $authenticated = true;
            }
        }

        if (!$authenticated) {
            throw ValidationException::withMessages([
                'code' => 'O código informado é inválido.',
            ]);
        }

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return to_route('dashboard');
    }
}
