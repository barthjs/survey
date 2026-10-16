<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\ViewResponse;
use App\Models\Response;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);
    $response = Response::factory()->create(['survey_id' => $survey->id]);

    Livewire::actingAs($user)
        ->test(ViewResponse::class, ['id' => $response->id])
        ->assertStatus(200);
});
