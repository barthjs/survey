<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Livewire\Pages\Survey\EditSurvey;
use App\Models\Survey;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(EditSurvey::class, ['id' => $survey->id])
        ->assertStatus(200);
});

it('denies guests from accessing the edit survey page', function () {
    $user = User::factory()->create();
    $guest = User::factory()->create();
    $survey = Survey::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($guest)
        ->test(EditSurvey::class, ['id' => $survey->id])
        ->assertForbidden();
});

it('can update a survey with valid data', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()
        ->for($user)
        ->hasQuestions(1, [
            'type' => QuestionType::TEXT,
            'question_text' => 'Old question?',
            'is_required' => false,
            'order_index' => 0,
        ])
        ->create();

    Livewire::actingAs($user);

    Livewire::test(EditSurvey::class, ['id' => $survey->id])
        ->set('title', 'Updated title')
        ->set('questions.0.question_text', 'New question text?')
        ->set('questions.0.type', QuestionType::TEXT)
        ->set('questions.0.is_required', true)
        ->call('updateSurvey')
        ->assertRedirect(route('surveys.view', $survey->id));

    $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'title' => 'Updated title']);
    $this->assertDatabaseHas('questions', [
        'survey_id' => $survey->id,
        'question_text' => 'New question text?',
        'is_required' => true,
    ]);
});

it('resets options when question type changes from multiple_choice to text', function () {
    $user = User::factory()->create();
    $survey = Survey::factory()
        ->for($user)
        ->hasQuestions(1, [
            'type' => QuestionType::MULTIPLE_CHOICE,
            'question_text' => 'Pick one',
            'is_required' => true,
            'order_index' => 0,
        ])
        ->create();

    $question = $survey->questions->first();
    $question->options()->createMany([
        ['option_text' => 'A', 'order_index' => 0],
        ['option_text' => 'B', 'order_index' => 1],
    ]);

    Livewire::actingAs($user);

    Livewire::test(EditSurvey::class, ['id' => $survey->id])
        ->call('handleQuestionTypeChange', 0, QuestionType::TEXT->value)
        ->assertSet('questions.0.options', []);
});
