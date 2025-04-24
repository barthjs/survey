<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\ViewSurvey;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ViewSurvey::class, ['id' => $survey->id])
        ->assertStatus(200);
});
