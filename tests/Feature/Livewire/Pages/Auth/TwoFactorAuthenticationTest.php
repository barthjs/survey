<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\TwoFactorChallenge;
use App\Livewire\Pages\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\assertAuthenticatedAs;

test('two factor authentication can be enabled', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('openConfirmTwoFactorAuthenticationModal')
        ->assertSet('confirmTwoFactorAuthenticationModal', true)
        ->assertSet('confirm_2fa_password', '')
        ->set('confirm_2fa_password', 'password')
        ->call('enableTwoFactorAuthentication')
        ->assertSet('confirmTwoFactorAuthenticationModal', false)
        ->assertSet('showingTwoFactorQrCode', true);

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_enabled_at)->toBeNull();
});

test('two factor authentication can be confirmed', function () {
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
    ]);

    $validCode = $google2fa->getCurrentOtp($secret);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('two_factor_code', $validCode)
        ->call('confirmTwoFactorAuthentication')
        ->assertHasNoErrors()
        ->assertSet('showingTwoFactorQrCode', false)
        ->assertSet('showingRecoveryCodes', true)
        ->assertSet('two_factor_code', '');

    $user->refresh();

    expect($user->two_factor_enabled_at)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->toHaveCount(10);
});

test('user with two factor enabled is redirected to challenge', function () {
    $user = User::factory()->create([
        'two_factor_enabled_at' => now(),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('two-factor'));

    $this->assertGuest();
});

test('user can authenticate with two factor code', function () {
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
        'two_factor_enabled_at' => now(),
    ]);

    $validCode = $google2fa->getCurrentOtp($secret);

    $this->withSession(['login.id' => $user->id]);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', $validCode)
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('surveys.index', absolute: false));

    assertAuthenticatedAs($user);
});

test('user can authenticate with recovery code', function () {
    $user = User::factory()->create([
        'two_factor_enabled_at' => now(),
        'two_factor_recovery_codes' => [Hash::make('recovery-code')],
    ]);

    $this->withSession(['login.id' => $user->id]);

    Livewire::test(TwoFactorChallenge::class)
        ->set('recovery', true)
        ->set('recovery_code', 'recovery-code')
        ->call('login')
        ->assertRedirect(route('surveys.index', absolute: false));

    assertAuthenticatedAs($user);

    $user->refresh();
    expect($user->two_factor_recovery_codes)->toBeEmpty();
});

test('two factor authentication can be disabled', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => [Hash::make('code')],
        'two_factor_enabled_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('openConfirmDisableTwoFactorAuthenticationModal')
        ->assertSet('confirmDisableTwoFactorAuthenticationModal', true)
        ->assertSet('confirm_2fa_password', '')
        ->set('confirm_2fa_password', 'password')
        ->call('disableTwoFactorAuthentication')
        ->assertHasNoErrors()
        ->assertSet('confirmDisableTwoFactorAuthenticationModal', false);

    $user->refresh();

    expect($user->two_factor_enabled_at)->toBeNull()
        ->and($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'two_factor_enabled_at' => now(),
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => [Hash::make('old-code')],
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('openConfirmRegenerateRecoveryCodesModal')
        ->assertSet('confirmRegenerateRecoveryCodesModal', true)
        ->assertSet('confirm_2fa_password', '')
        ->set('confirm_2fa_password', 'password')
        ->call('regenerateRecoveryCodes')
        ->assertHasNoErrors()
        ->assertSet('confirmRegenerateRecoveryCodesModal', false)
        ->assertSet('showingRecoveryCodes', true);

    $user->refresh();

    expect($user->two_factor_recovery_codes)->toHaveCount(10)
        ->and(Hash::check('old-code', $user->two_factor_recovery_codes[0]))->toBeFalse();
});
