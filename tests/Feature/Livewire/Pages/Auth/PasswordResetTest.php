<?php

declare(strict_types=1);

use App\Jobs\SendPasswordResetLinkJob;
use App\Livewire\Pages\Auth\ForgotPassword;
use App\Livewire\Pages\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();
    Queue::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Queue::assertPushed(SendPasswordResetLinkJob::class, function ($job) use ($user) {
        return $job instanceof SendPasswordResetLinkJob
            && $job->uniqueId() === mb_strtolower($user->email);
    });

    $jobInstance = new SendPasswordResetLinkJob(['email' => $user->email]);
    $jobInstance->handle();

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();
    Queue::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Queue::assertPushed(SendPasswordResetLinkJob::class, function ($job) use ($user) {
        return $job instanceof SendPasswordResetLinkJob
            && $job->uniqueId() === mb_strtolower($user->email);
    });

    $jobInstance = new SendPasswordResetLinkJob(['email' => $user->email]);
    $jobInstance->handle();

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();
    Queue::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendPasswordResetLink');

    Queue::assertPushed(SendPasswordResetLinkJob::class, function ($job) use ($user) {
        return $job instanceof SendPasswordResetLinkJob
            && $job->uniqueId() === mb_strtolower($user->email);
    });

    $jobInstance = new SendPasswordResetLinkJob(['email' => $user->email]);
    $jobInstance->handle();

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});
