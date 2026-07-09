<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationService
{
    public function __construct(
        protected Google2FA $google2fa = new Google2FA(),
    ) {}

    /**
     * Estado do 2FA para a tela de perfil. O QR/segredo só são expostos durante
     * a configuração (antes de confirmar); os códigos de recuperação ficam
     * disponíveis enquanto o 2FA estiver configurado.
     *
     * @return array<string, mixed>
     */
    public function stateFor(User $user): array
    {
        $isConfiguring = $user->two_factor_secret !== null && !$user->hasTwoFactorEnabled();

        return [
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirming' => $isConfiguring,
            'qr_svg' => $isConfiguring
                ? $this->qrCodeSvg((string) config('app.name'), $user->email, (string) $user->two_factor_secret)
                : null,
            'secret' => $isConfiguring ? $user->two_factor_secret : null,
            'recovery_codes' => $user->two_factor_secret !== null ? $user->recoveryCodes() : [],
        ];
    }

    /**
     * Resolve um desafio de dois fatores para o usuário: aceita um código TOTP
     * ou um código de recuperação de uso único (consumido ao ser usado).
     *
     * Concentra a regra de verificação — o controller cuida apenas do fluxo de
     * sessão/redirecionamento.
     */
    public function challenge(User $user, ?string $code, ?string $recoveryCode): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        if (filled($code)) {
            $normalized = (string) preg_replace('/\s+/', '', $code);

            return $this->verify($user->two_factor_secret, $normalized);
        }

        if (filled($recoveryCode)) {
            $recoveryCode = mb_trim($recoveryCode);

            // Comparação constant-time para evitar timing attack.
            $match = collect($user->recoveryCodes())
                ->first(fn (string $stored): bool => hash_equals($stored, $recoveryCode));

            if ($match !== null) {
                $user->replaceRecoveryCode($match);

                return true;
            }
        }

        return false;
    }

    /**
     * Gera uma nova chave secreta TOTP (base32).
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Verifica um código TOTP de 6 dígitos contra a chave secreta.
     */
    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Monta a URL otpauth:// usada pelos apps autenticadores.
     */
    public function otpauthUrl(string $company, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($company, $email, $secret);
    }

    /**
     * Renderiza o QR Code (SVG) da URL otpauth para exibir no perfil.
     */
    public function qrCodeSvg(string $company, string $email, string $secret): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200, 1),
            new SvgImageBackEnd(),
        );

        return (new Writer($renderer))->writeString(
            $this->otpauthUrl($company, $email, $secret),
        );
    }

    /**
     * Gera códigos de recuperação de uso único.
     *
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn (): string => sprintf(
                '%s-%s',
                mb_strtoupper(bin2hex(random_bytes(5))),
                mb_strtoupper(bin2hex(random_bytes(5))),
            ))
            ->all();
    }
}
