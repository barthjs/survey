<?php

declare(strict_types=1);

use App\Console\Commands\ResetPasswordCommand;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('oldpassword'),
    ]);
});

it('resets the password via email', function (): void {
    $newPassword = 'newpassword123';

    $this->artisan(ResetPasswordCommand::class, ['email' => $this->user->email])
        ->expectsConfirmation(__('Resetting password for user: ').$this->user->name, 'yes')
        ->expectsQuestion(__('Enter new password for user: ').$this->user->name, $newPassword)
        ->expectsQuestion(__('Confirm password'), $newPassword)
        ->assertExitCode(0);

    $this->user->refresh();
    expect(Hash::check($newPassword, $this->user->password))->toBeTrue();
});

it('fails when user is not found', function (): void {
    $this->artisan(ResetPasswordCommand::class, ['email' => 'nonexistent@example.com'])
        ->expectsOutput(__('User not found'))
        ->assertExitCode(1);
});
