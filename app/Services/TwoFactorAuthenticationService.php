<?php

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final readonly class TwoFactorAuthenticationService
{
    public function __construct(
        private Google2FA $google2fa
    ) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get the QR code SVG for the given secret key.
     */
    public function getQRCodeSvg(string $company, string $holder, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl($company, $holder, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($url);
    }

    /**
     * Verify the given OTP for the given secret.
     */
    public function verify(?string $secret, string $code): bool
    {
        if (empty($secret)) {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code) !== false;
    }

    /**
     * Generate 10 new recovery codes.
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return array_map(
            fn (): string => Str::random(10).'-'.Str::random(10),
            range(1, 10)
        );
    }

    /**
     * Hash the given recovery codes.
     *
     * @param  array<int, string>  $codes
     * @return array<int, string>
     */
    public function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code): string => Hash::make($code), $codes);
    }
}
