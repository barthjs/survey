<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Actions\Logout;
use App\Models\User;
use App\Notifications\VerifyNewEmailNotification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    use Toast;

    public string $name = '';

    public string $email = '';

    public string $new_email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $confirmUserDeletionModal = false;

    public bool $rateLimited = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->new_email = Auth::user()->new_email ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $this->email = mb_strtolower($this->email);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->name = $validated['name'];
        $validated['email'] = mb_strtolower($validated['email']);

        if ($user->email !== $validated['email']) {
            if (config('app.enable_email_verification')) {
                $user->new_email = $validated['email'];
                $user->save();
                $this->sendVerification();
            } else {
                $user->email = $validated['email'];
                $user->save();
                $this->success(__('Profile information updated'));
            }
        }
    }

    public function sendVerification(): void
    {
        if (! config('app.enable_email_verification')) {
            abort(404);
        }

        $user = Auth::user();
        if (empty($user->new_email)) {
            $this->error(__('Email already verified.'));

            return;
        }

        $key = 'send-new-verification-email:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->rateLimited = true;
            $this->error(__('Too many verification requests. Please try again later.'));

            return;
        }

        RateLimiter::hit($key);

        Notification::route('mail', $user->new_email)->notify(new VerifyNewEmailNotification($user, $user->new_email));

        $this->success(__('Verification email sent. Please check your email.'));
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->success(__('Password updated'));
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect(route('home'), navigate: true);
    }

    public function openConfirmUserDeletionModal(): void
    {
        $this->password = '';
        $this->confirmUserDeletionModal = true;
    }

    public function closeConfirmUserDeletionModal(): void
    {
        $this->confirmUserDeletionModal = false;
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.profile')
            ->title(__('Profile'));
    }
}
