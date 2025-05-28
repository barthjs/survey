<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('Invalid login credentials.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('surveys.index', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render(): Application|Factory|View
    {
        /*
         * Handles the case where a user opens the email verification link in a different browser
         * where they are not logged in. If the intended URL is the verification page,
         * show a notice after login to clarify why they were redirected here.
         */
        $wantsToVerifyEmail = false;
        $intendedUrl = session('url.intended');
        if (($intendedUrl) && (Str::contains($intendedUrl, route('verification.notice', absolute: false)))) {
            $wantsToVerifyEmail = true;

            // Clear the intended URL to prevent the message from showing again later
            session()->forget('url.intended');
        }

        return view('livewire.pages.auth.login')
            ->with('wantsToVerifyEmail', $wantsToVerifyEmail)
            ->title(__('Log in'));
    }
}
