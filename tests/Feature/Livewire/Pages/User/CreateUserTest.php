<?php

declare(strict_types=1);

use App\Livewire\Pages\User\CreateUser;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateUser::class)
        ->assertStatus(200);
});
