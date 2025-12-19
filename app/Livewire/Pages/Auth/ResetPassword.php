<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        if (! config()->boolean('app.enable_password_reset')) {
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

        /** @var string $status */
        $status = Password::reset([
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
        ], function (User $user): void {
            $user->password = Hash::make($this->password);
            $user->remember_token = null;
            $user->save();

            DB::table(config()->string('session.table'))
                ->where('user_id', $user->id)
                ->delete();
        });

        if ($status !== Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.auth.reset-password')
            ->title(__('Reset password'));
    }
}
