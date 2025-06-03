<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class VerifyEmailController
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if (! config('app.enable_email_verification')) {
            abort(404);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('surveys.index', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            /** @var User $user */
            $user = $request->user();

            RateLimiter::clear('send-verification-email:'.$user->email);

            event(new Verified($user));
        }

        return redirect()->intended(route('surveys.index', absolute: false).'?verified=1');
    }
}
