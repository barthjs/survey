<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\Factory;
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
final class Login extends Component
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

        $user = $this->validateCredentials();

        /** @var User $user */
        if ($user->two_factor_enabled_at) {
            Session::put('login.id', $user->id);
            Session::put('login.remember', $this->remember);

            $this->redirect(route('two-factor'), navigate: true);

            return;
        }

        Auth::login($user, remember: $this->remember);
        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('surveys.index', absolute: false), navigate: true);
    }

    public function render(): Factory|View
    {
        /*
         * Handles the case where a user opens the email verification link in a different browser
         * where they are not logged in. Show a notice to clarify why they were redirected here.
         */
        $wantsToVerifyEmail = false;
        $intendedUrl = session('url.intended');
        if (is_string($intendedUrl) && (Str::contains($intendedUrl, route('verification.notice', absolute: false)))) {
            $wantsToVerifyEmail = true;
        }

        return view('livewire.pages.auth.login')
            ->with('wantsToVerifyEmail', $wantsToVerifyEmail)
            ->title(__('Login'));
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Validate the user's credentials.
     */
    private function validateCredentials(): Authenticatable
    {
        $user = Auth::getProvider()->retrieveByCredentials(['email' => $this->email, 'password' => $this->password]);

        if (! $user || ! Auth::getProvider()->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('Invalid login credentials.'),
            ]);
        }

        return $user;
    }
}
