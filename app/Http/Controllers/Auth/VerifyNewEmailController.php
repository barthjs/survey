<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

final class VerifyNewEmailController
{
    public function __invoke(string $id, string $hash): RedirectResponse
    {
        if (! config('app.enable_email_verification')) {
            abort(404);
        }

        $user = Auth::user();

        if (empty($user->new_email)) {
            Session::flash('new_email', __('New email already verified.'));

            return redirect()->route('profile');
        }

        $calculatedHash = sha1($user->new_email.$user->id.config('app.key'));

        if ($user->id !== $id || ! hash_equals($calculatedHash, $hash)) {
            Session::flash('new_email', __('Invalid verification link.'));

            return redirect()->route('profile');
        }

        $testUser = User::whereEmail($user->new_email)->first();
        if (! empty($testUser)) {
            $user->new_email = null;
            $user->save();

            Session::flash('new_email', __('The email address is already in use.'));

            return redirect()->route('profile');
        }

        $user->email = $user->new_email;
        $user->new_email = null;
        $user->email_verified_at = now();
        $user->save();

        RateLimiter::clear('send-new-verification-email:'.$user->email);

        Session::flash('new_email', __('Email address verified.'));

        return redirect()->route('profile');
    }
}
