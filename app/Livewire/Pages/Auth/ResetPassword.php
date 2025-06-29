<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        if (! config('app.enable_password_reset')) {
            abort(404);
        }

        $this->token = $token;

        $this->email = (string) request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful, we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise, we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error, we can
        // redirect them back to where they came from with their error message.
        if ($status !== Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        RateLimiter::clear('password-reset:'.Str::lower($this->email));

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.auth.reset-password')
            ->title(__('Reset password'));
    }
}
