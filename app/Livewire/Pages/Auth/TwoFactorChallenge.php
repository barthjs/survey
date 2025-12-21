<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class TwoFactorChallenge extends Component
{
    public string $code = '';

    public string $recovery_code = '';

    public bool $recovery = false;

    public function mount(): void
    {
        if (! Session::has('login.id')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function login(TwoFactorAuthenticationService $service): void
    {
        $userId = Session::get('login.id');
        if (! $userId) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        /** @var User $user */
        $user = User::findOrFail($userId);

        if ($this->recovery) {
            $this->validate(['recovery_code' => ['required', 'string']]);

            if (! $user->consumeRecoveryCode($this->recovery_code)) {
                throw ValidationException::withMessages([
                    'recovery_code' => [__('The provided recovery code was invalid.')],
                ]);
            }
        } else {
            $this->validate(['code' => ['required', 'string']]);

            if (! $service->verify($user->two_factor_secret, $this->code)) {
                throw ValidationException::withMessages([
                    'code' => [__('The provided two factor authentication code was invalid.')],
                ]);
            }
        }

        Auth::login($user, remember: (bool) Session::get('login.remember', false));

        Session::forget(['login.id', 'login.remember']);
        Session::regenerate();

        $this->redirectIntended(default: route('surveys.index', absolute: false), navigate: true);
    }

    public function toggleRecovery(): void
    {
        $this->recovery = ! $this->recovery;
        $this->code = '';
        $this->recovery_code = '';
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.auth.two-factor-challenge')
            ->title(__('Two-factor Confirmation'));
    }
}
