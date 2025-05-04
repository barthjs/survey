<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->email = mb_strtolower($this->email);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if (User::whereIsAdmin(true)->count() === 0) {
            $validated['is_admin'] = true;
        }

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('surveys.index', absolute: false), navigate: true);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.auth.register')
            ->title(__('Register'));
    }
}
