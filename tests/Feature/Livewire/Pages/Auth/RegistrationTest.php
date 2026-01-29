<?php

declare(strict_types=1);

use App\Livewire\Pages\Auth\Register;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the register page', function () {
    livewire(Register::class)
        ->assertOk();
});

it('can register a new user', function () {
    livewire(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('surveys.index'));

    assertAuthenticated();
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'user@example.com']);

    livewire(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'user@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors(['email']);
});

test('oidc callback creates new user and redirects to dashboard', function () {
    Socialite::fake('oidc', (new SocialiteUser)->map([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'user@example.com',
    ]));

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(route('surveys.index', absolute: false));

    assertDatabaseHas('sys_users', [
        'name' => 'Test User',
        'email' => 'user@example.com',
    ]);

    assertDatabaseHas('sys_user_providers', [
        'provider_name' => 'oidc',
        'provider_id' => '1',
    ]);

    assertAuthenticated();
});
