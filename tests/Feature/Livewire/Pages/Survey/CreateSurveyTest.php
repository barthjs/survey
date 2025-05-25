<?php

declare(strict_types=1);

use App\Livewire\Pages\Survey\CreateSurvey;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateSurvey::class)
        ->assertStatus(200);
});

it('can create a survey with valid data', function () {
    $user = User::factory()->create();

    $questions = [
        [
            'question_text' => 'What is your name?',
            'type' => 'TEXT',
            'is_required' => true,
            'options' => [],
        ],
        [
            'question_text' => 'Choose your favorite colors',
            'type' => 'MULTIPLE_CHOICE',
            'is_required' => false,
            'options' => ['Red', 'Green', 'Blue'],
        ],
        [
            'question_text' => 'Choose your favorite colors',
            'type' => 'FILE',
            'is_required' => false,
            'options' => [],
        ],
    ];

    Livewire::actingAs($user)
        ->test(CreateSurvey::class)
        ->set('title', 'Test Survey')
        ->set('description', 'A simple test survey')
        ->set('end_date', now()->addDays(7)->toDateTimeString())
        ->set('questions', $questions)
        ->call('createSurvey')
        ->assertHasNoErrors();

    expect(Survey::count())->toBe(1);
    $survey = Survey::with('questions.options')->first();

    expect($survey->questions)->toHaveCount(3)
        ->and($survey->questions[1]->options)->toHaveCount(3);
});
