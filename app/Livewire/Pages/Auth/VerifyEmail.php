<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Actions\Logout;
use App\Jobs\SendEmailVerificationJob;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class VerifyEmail extends Component
{
    public function mount(): void
    {
        if (! config()->boolean('app.enable_email_verification')) {
            abort(404);
        }

        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('surveys.index', absolute: false), navigate: true);
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $key = 'email-verification:'.$user->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            Session::flash('status', __('Too many verification requests. Please try again later.'));

            return;
        }

        RateLimiter::hit($key, 3600);

        SendEmailVerificationJob::dispatch($user, app()->getLocale());

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

    public function render(): Factory|View
    {
        return view('livewire.pages.auth.verify-email')
            ->title(__('Verify Email Address'));
    }
}
