<?php

declare(strict_types=1);

use App\Actions\Logout;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\VerifyNewEmailController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Pages\Auth\ForgotPassword;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\Register;
use App\Livewire\Pages\Auth\ResetPassword;
use App\Livewire\Pages\Auth\TwoFactorChallenge;
use App\Livewire\Pages\Auth\VerifyEmail;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', Register::class)
        ->name('register');

    Route::get('/login', Login::class)
        ->name('login');

    Route::get('/two-factor-challenge', TwoFactorChallenge::class)
        ->name('two-factor');

    Route::get('/forgot-password', ForgotPassword::class)
        ->name('password.request');

    Route::get('/reset-password/{token}', ResetPassword::class)
        ->name('password.reset');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/verify-email', VerifyEmail::class)
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('/verify-new-email/{id}/{hash}', VerifyNewEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify-new-email');

    Route::get('/user/verified', function () {
        return response()->json([
            'verified' => auth()->user()->hasVerifiedEmail(),
        ]);
    })->name('verification.verified');
});

Route::post('/logout', Logout::class)
    ->name('logout');
