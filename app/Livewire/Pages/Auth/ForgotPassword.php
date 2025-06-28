<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Jobs\SendPasswordResetLinkJob;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    public function mount(): void
    {
        if (! config('app.enable_password_reset')) {
            abort(404);
        }
    }

    /**
     * Send a password-reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate();

        $key = 'password-reset:'.Str::lower($this->email);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            $this->addError('email', __('Too many reset attempts. Try again in :minutes minutes.', [
                'minutes' => ceil($seconds / 60),
            ]));

            return;
        }

        RateLimiter::hit($key, 3600);

        SendPasswordResetLinkJob::dispatch($this->only('email'), app()->getLocale());

        Session::flash('status', __('A reset link will be sent if the account exists.'));
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.auth.forgot-password')
            ->title(__('Forgot password'));
    }
}
