<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'two_factor_secret' => Str::random(10),
        'two_factor_recovery_codes' => [Str::random(10), Str::random(10)],
        'two_factor_enabled_at' => now(),
    ]);
});

it('disables 2fa successfully for an existing user', function () {
    artisan('app:disable-2fa', ['email' => 'test@example.com'])
        ->expectsConfirmation(__('Disabling 2FA for user: ').$this->user->email, 'yes')
        ->expectsOutput(__('Two factor authentication disabled.'))
        ->assertExitCode(0);

    $this->user->refresh();
    expect($this->user->two_factor_secret)->toBeNull()
        ->and($this->user->two_factor_recovery_codes)->toBeEmpty()
        ->and($this->user->two_factor_enabled_at)->toBeNull();
});

it('fails when user is not found', function () {
    artisan('app:disable-2fa', ['email' => 'nonexistent@example.com'])
        ->expectsOutput(__('User not found'))
        ->assertExitCode(1);
});
