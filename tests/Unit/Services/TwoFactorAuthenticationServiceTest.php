<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;

test('it generates 10 recovery codes', function () {
    $service = new TwoFactorAuthenticationService(new Google2FA());

    $codes = $service->generateRecoveryCodes();

    expect($codes)->toHaveCount(10);
});
