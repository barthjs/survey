<?php

declare(strict_types=1);

use App\Livewire\Pages\User\EditUser;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(EditUser::class, ['id' => $user->id])
        ->assertStatus(200);
});
