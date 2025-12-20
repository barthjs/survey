<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController
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
            return redirect()->intended(route('surveys.index', absolute: false));
        }

        $request->fulfill();

        return redirect()->intended(route('surveys.index', absolute: false));
    }
}
