<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Livewire\Pages\Survey\SubmitSurvey;
use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

it('rejects text inputs longer than 255 characters', function () {
    $user = User::factory()->create();

    $survey = Survey::factory()
        ->for($user)
        ->create(['is_active' => true]);

    $question = Question::factory()->for($survey)->create([
        'type' => QuestionType::TEXT,
        'is_required' => true,
        'order_index' => 0,
    ]);

    $tooLongText = str_repeat('a', 256);

    Livewire::test(SubmitSurvey::class, ['id' => $survey->id])
        ->set("response.$question->id", $tooLongText)
        ->call('submitSurvey')
        ->assertHasErrors(["response.$question->id" => 'max']);
});

it('rejects invalid multiple-choice option IDs', function () {
    $user = User::factory()->create();

    $survey = Survey::factory()
        ->for($user)
        ->create(['is_active' => true]);

    $question = Question::factory()
        ->for($survey)
        ->create([
            'type' => QuestionType::MULTIPLE_CHOICE,
            'is_required' => true,
            'order_index' => 0,
        ]);

    QuestionOption::factory(['question_id' => $question->id, 'order_index' => 0]);

    $invalidOptionId = fake()->uuid;

    Livewire::test(SubmitSurvey::class, ['id' => $survey->id])
        ->set("response.$question->id", [$invalidOptionId => true])
        ->call('submitSurvey')
        ->assertHasErrors(["response.$question->id"]);
});

it('rejects invalid file uploads with disallowed MIME types', function () {
    $user = User::factory()->create();

    $survey = Survey::factory()
        ->for($user)
        ->create(['is_active' => true]);

    $question = Question::factory()->for($survey)->create([
        'type' => QuestionType::FILE,
        'is_required' => true,
        'order_index' => 0,
    ]);

    $invalidFile = UploadedFile::fake()->create('valid-file.txt', 100, 'application/x-msdownload');

    Livewire::test(SubmitSurvey::class, ['id' => $survey->id])
        ->set("response.$question->id", $invalidFile)
        ->call('submitSurvey')
        ->assertHasErrors(["response.$question->id"]);
});

it('submits a valid survey with text, multiple choice, and file', function () {
    Storage::fake();

    $user = User::factory()->create();
    $survey = Survey::factory()->for($user)->create(['is_active' => true]);

    $textQuestion = Question::factory()->create([
        'survey_id' => $survey->id,
        'type' => QuestionType::TEXT,
        'is_required' => true,
        'order_index' => 0,
    ]);

    $multipleChoiceQuestion = Question::factory()->create([
        'survey_id' => $survey->id,
        'type' => QuestionType::MULTIPLE_CHOICE,
        'is_required' => true,
        'order_index' => 1,
    ]);

    $option = QuestionOption::factory()->create([
        'question_id' => $multipleChoiceQuestion->id,
        'order_index' => 0,
    ]);

    $fileQuestion = Question::factory()->for($survey)->create([
        'survey_id' => $survey->id,
        'type' => QuestionType::FILE,
        'is_required' => true,
        'order_index' => 2,
    ]);

    $validFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::test(SubmitSurvey::class, ['id' => $survey->id])
        ->set("response.{$textQuestion->id}", 'Some valid text')
        ->set("response.{$multipleChoiceQuestion->id}", [$option->id => true])
        ->set("response.{$fileQuestion->id}", $validFile)
        ->call('submitSurvey')
        ->assertRedirect(route('surveys.thank-you'));

    expect(\App\Models\Response::count())->toBe(1)
        ->and(Answer::count())->toBe(3)
        ->and(AnswerOption::count())->toBe(1);
});
