<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Jobs\SendEmailVerificationJob;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Session;

#[Layout('components.layouts.auth')]
final class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if (! config()->boolean('app.allow_registration')) {
            abort(404);
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->email = mb_strtolower($this->email);

        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if (User::where('is_admin')->count() === 0) {
            $validated['is_admin'] = true;
        }

        $user = User::create($validated);
        if (config()->boolean('app.enable_email_verification')) {
            SendEmailVerificationJob::dispatch($user, app()->getLocale());
        }

        Auth::login($user);
        Session::regenerate();

        $this->redirect(route('surveys.index', absolute: false), navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.auth.register')
            ->title(__('Register'));
    }
}
