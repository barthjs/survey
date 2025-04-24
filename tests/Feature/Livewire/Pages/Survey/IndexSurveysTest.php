<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\IndexSurveys;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    Survey::factory()->count(5)->create(['user_id' => $user->id]);

    Livewire::test(IndexSurveys::class)
        ->assertStatus(200);
});
