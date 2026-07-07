<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function __invoke(Request $request, TwoFactorAuthenticationService $twoFactor): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'twoFactor' => $this->twoFactorState($user, $twoFactor),
        ]);
    }

    /**
     * Estado do 2FA para a tela de perfil. O QR/segredo só são expostos durante
     * a configuração (antes de confirmar); os códigos de recuperação ficam
     * disponíveis enquanto o 2FA estiver configurado.
     *
     * @return array<string, mixed>
     */
    private function twoFactorState(User $user, TwoFactorAuthenticationService $service): array
    {
        $isConfiguring = $user->two_factor_secret !== null && !$user->hasTwoFactorEnabled();

        return [
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirming' => $isConfiguring,
            'qr_svg' => $isConfiguring
                ? $service->qrCodeSvg((string) config('app.name'), $user->email, (string) $user->two_factor_secret)
                : null,
            'secret' => $isConfiguring ? $user->two_factor_secret : null,
            'recovery_codes' => $user->two_factor_secret !== null ? $user->recoveryCodes() : [],
        ];
    }
}
