<?php

declare(strict_types=1);

use App\Livewire\Pages\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('logs out other browser sessions on the profile page', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $otherSessionId = 'other-session-id';
    DB::table(config()->string('session.table'))->insert([
        'id' => $otherSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'TestAgent',
        'last_activity' => now()->timestamp,
        'payload' => [],
    ]);

    $this->assertDatabaseHas(config()->string('session.table'), [
        'id' => $otherSessionId,
        'user_id' => $user->id,
    ]);

    Livewire::test(Profile::class)
        ->set('confirm_logout_password', 'password')
        ->call('logoutOtherBrowserSessions')
        ->assertSet('confirmLogoutOtherBrowserSessionsModal', false)
        ->assertSee('All other browser sessions have been logged out successfully.');

    $this->assertDatabaseMissing(config()->string('session.table'), [
        'id' => $otherSessionId,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas(config()->string('session.table'), [
        'id' => session()->getId(),
        'user_id' => $user->id,
    ]);
});
