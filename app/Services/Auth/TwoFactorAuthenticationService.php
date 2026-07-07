<?php

declare(strict_types=1);

namespace App\Services\Auth;

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
