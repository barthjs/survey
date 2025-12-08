<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Jobs\SendEmailVerificationJob;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class VerifyEmail extends Component
{
    public bool $rateLimited = false;

    public function mount(): void
    {
        if (! config('app.enable_email_verification')) {
            abort(404);
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (! config('app.enable_email_verification')) {
            abort(404);
        }

        $user = Auth::user();

        $key = 'send-verification-email:'.$user->email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->rateLimited = true;

            Session::flash('status', __('Too many verification requests. Please try again later.'));

            return;
        }

        $this->rateLimited = false;

        RateLimiter::hit($key, 3600);

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('surveys.index', absolute: false), navigate: true);

            return;
        }

        SendEmailVerificationJob::dispatch(Auth::user(), app()->getLocale());

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('home'), navigate: true);
    }
}
