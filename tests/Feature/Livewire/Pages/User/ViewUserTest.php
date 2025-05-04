<?php

declare(strict_types=1);

use App\Livewire\Pages\User\ViewUser;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ViewUser::class, ['id' => $user->id])
        ->assertStatus(200);
});
