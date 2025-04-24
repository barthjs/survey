<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\CreateSurvey;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateSurvey::class)
        ->assertStatus(200);
});
