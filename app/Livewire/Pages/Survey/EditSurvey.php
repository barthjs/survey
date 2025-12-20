<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\AnswerOption;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Traits\ConfirmDeletionModal;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
final class EditSurvey extends Component
{
    use ConfirmDeletionModal, Toast;

    public Survey $survey;

    public string $title;

    public ?string $description = null;

    public bool $is_public;

    public bool $is_active;

    public ?string $end_date = null;

    /**
     * @var array<int, array{
     *     id?: string,
     *     question_text: string,
     *     type: string,
     *     is_required: bool,
     *     options: array<int, array{
     *         id?: string,
     *         option_text: string
     *     }>
     * }>
     */
    public array $questions;

    public function mount(string $id): void
    {
        $this->survey = Survey::with('questions.options')->findOrFail($id);
        if (auth()->user()->cannot('update', $this->survey)) {
            abort(403);
        }

        $this->title = $this->survey->title;
        $this->description = $this->survey->description;
        $this->is_public = $this->survey->is_public;
        $this->is_active = $this->survey->is_active;
        $this->end_date = $this->survey->end_date?->format('Y-m-d\TH:i');

        /** @phpstan-ignore-next-line */
        $this->questions = $this->survey->questions->map(fn (Question $question) => [
            'id' => $question->id,
            'question_text' => $question->question_text,
            'type' => $question->type,
            'is_required' => $question->is_required,
            'options' => $question->type === QuestionType::MULTIPLE_CHOICE
                ? $question->options->map(fn (QuestionOption $option) => [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                ])->toArray()
                : [],
        ])->toArray();
    }

    public function save(): void
    {
        $validatedSurveyData = $this->validateData();

        try {
            Question::validateQuestions($this->questions);
        } catch (ValidationException $e) {
            $this->dispatch('validationErrors', $e->errors());

            return;
        }

        DB::transaction(function () use ($validatedSurveyData): void {
            $this->survey->update($validatedSurveyData);

            $existingQuestions = $this->survey
                ->questions()
                ->with('options')
                ->get()
                ->keyBy('id');

            /** @var array<int, string> $submittedQuestionIds */
            $submittedQuestionIds = collect($this->questions)
                ->pluck('id')
                ->filter()
                ->all();

            $questionsToDelete = $existingQuestions->keys()->diff($submittedQuestionIds);

            foreach ($questionsToDelete as $questionId) {
                $question = $existingQuestions->get($questionId);

                if ($question) {
                    $question->delete();
                }
            }

            foreach ($this->questions as $questionIndex => $questionData) {
                $question = null;

                if (isset($questionData['id'])) {
                    $question = $existingQuestions->get($questionData['id']);
                }

                if ($question) {
                    $question->update([
                        'question_text' => $questionData['question_text'],
                        'type' => $questionData['type'],
                        'is_required' => $questionData['is_required'],
                        'order_index' => $questionIndex,
                    ]);
                } else {
                    $question = $this->survey->questions()->create([
                        'question_text' => $questionData['question_text'],
                        'type' => $questionData['type'],
                        'is_required' => $questionData['is_required'],
                        'order_index' => $questionIndex,
                    ]);
                }

                if ($questionData['type'] !== QuestionType::MULTIPLE_CHOICE->value) {
                    continue;
                }

                $existingOptions = $question->options->keyBy('id');
                $submittedOptions = collect($questionData['options']);

                foreach ($submittedOptions as $optionIndex => $optionData) {
                    if (isset($optionData['id'])) {
                        $option = $existingOptions->get($optionData['id']);
                        if ($option) {
                            $option->update([
                                'option_text' => $optionData['option_text'],
                                'order_index' => $optionIndex,
                            ]);
                        }

                        continue;
                    }

                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'order_index' => $optionIndex,
                    ]);
                }

                /** @var array<int, string> $submittedOptionIds */
                $submittedOptionIds = $submittedOptions->pluck('id')->filter()->all();
                $optionsToDelete = $existingOptions->keys()->diff($submittedOptionIds);

                foreach ($optionsToDelete as $optionId) {
                    /** @var QuestionOption $option */
                    $option = $existingOptions->get($optionId);

                    $option->answerOptions->each(function (AnswerOption $answerOption) {
                        $answerOption->answer()->delete();
                    });

                    $option->delete();
                }
            }
        });

        $this->success(__('Updated survey'));

        $this->redirect(route('surveys.view', $this->survey->id), navigate: true);
    }

    public function deleteSurvey(): void
    {
        if (auth()->user()->cannot('delete', $this->survey)) {
            abort(403);
        }

        $this->survey->delete();

        $this->closeConfirmDeletionModal();
        $this->warning(__('Deleted survey'));

        $this->redirect(route('surveys.index'), navigate: true);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.edit')
            ->title(__('Edit survey'));
    }

    /**
     * @return array{
     *     title: string,
     *     description: ?string,
     *     is_public: bool,
     *     is_active: bool,
     *     end_date: ?CarbonInterface,
     * }
     */
    private function validateData(): array
    {
        $this->validate(Survey::getValidationRules());

        return [
            'title' => mb_trim($this->title),
            'description' => $this->description,
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date) : null,
        ];
    }
}
