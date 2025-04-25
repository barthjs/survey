<?php

declare(strict_types=1);

use App\Livewire\Pages\User\IndexUsers;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(IndexUsers::class)
        ->assertStatus(200);
});
