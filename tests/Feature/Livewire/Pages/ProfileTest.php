<?php

declare(strict_types=1);

use App\Livewire\Pages\Profile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    asUser();

    $this->user = auth()->user();
});

it('renders the profile page', function () {
    Livewire::test(Profile::class)
        ->assertOk()
        ->assertSet('name', $this->user->name)
        ->assertSet('email', $this->user->email);
});

it('can update profile information', function () {
    Livewire::test(Profile::class)
        ->set('name', 'Updated Name')
        ->set('email', 'updated@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    assertDatabaseHas('sys_users', [
        'id' => $this->user->id,
        'name' => 'Updated Name',
        'new_email' => 'updated@example.com',
    ]);
});

it('can update the password', function () {
    Livewire::test(Profile::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('current_password', '')
        ->assertSet('password', '')
        ->assertSet('password_confirmation', '');

    $this->user->refresh();
    expect(Hash::check('new-password', $this->user->password))->toBeTrue();
});

it('requires the correct current password to update password', function () {
    Livewire::test(Profile::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect(Hash::check('password', $this->user->refresh()->password))->toBeTrue();
});

it('can remove a linked OIDC provider', function () {
    $provider = $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '1']);

    Livewire::test(Profile::class)
        ->set('selectedProviderId', $provider->id)
        ->call('removeProvider', $provider->id)
        ->assertHasNoErrors();

    assertDatabaseMissing('sys_user_providers', ['id' => $provider->id]);
});

it('can delete the user account', function () {
    Livewire::test(Profile::class)
        ->set('confirm_delete_password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    assertDatabaseMissing('sys_users', ['id' => $this->user->id]);
});

it('requires correct password for account deletion', function () {
    Livewire::test(Profile::class)
        ->set('confirm_delete_password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['confirm_delete_password']);

    assertDatabaseHas('sys_users', ['id' => $this->user->id]);
});
