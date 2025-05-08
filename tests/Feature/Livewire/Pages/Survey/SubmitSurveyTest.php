<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Livewire\Pages\Survey\SubmitSurvey;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()
        ->for($user)
        ->hasQuestions(1, [
            'type' => QuestionType::TEXT,
            'question_text' => 'Test question?',
            'is_required' => true,
            'order_index' => 0,
        ])
        ->create(['is_active' => true]);

    Livewire::test(SubmitSurvey::class, ['id' => $survey->id])
        ->assertStatus(200);
});
