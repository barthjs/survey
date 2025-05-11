<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\Survey;
use App\Traits\ConfirmDeletionModal;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;
use Throwable;
use ValueError;

#[Layout('components.layouts.app')]
class EditSurvey extends Component
{
    use ConfirmDeletionModal, Toast;

    public Survey $survey;

    public string $title = '';

    public ?string $description = null;

    public ?string $closed_at = null;

    public bool $is_active;

    public array $questions = [];

    public function mount(string $id): void
    {
        $this->survey = Survey::with('questions.options')->findOrFail($id);

        if (auth()->user()->cannot('update', $this->survey)) {
            abort(403);
        }

        $this->title = $this->survey->title;
        $this->description = $this->survey->description;
        $this->closed_at = $this->survey->closed_at?->format('Y-m-d\TH:i');
        $this->is_active = $this->survey->is_active;

        $this->questions = $this->survey->questions->map(fn ($question) => [
            'id' => $question->id,
            'question_text' => $question->question_text,
            'type' => $question->type,
            'is_required' => $question->is_required,
            'options' => $question->type === QuestionType::MULTIPLE_CHOICE
                ? $question->options->map(fn ($opt) => ['id' => $opt->id, 'option_text' => $opt->option_text])->toArray()
                : [],
        ])->toArray();
    }

    public function addQuestion(): void
    {
        $isRequired = count($this->questions) === 0;

        $this->questions[] = [
            'question_text' => '',
            'type' => QuestionType::TEXT,
            'is_required' => $isRequired,
            'options' => [],
        ];
    }

    public function removeQuestion(int $questionIndex): void
    {
        if (! isset($this->questions[$questionIndex])) {
            return;
        }

        if (count($this->questions) > 1) {
            array_splice($this->questions, $questionIndex, 1);

            if (count($this->questions) === 1) {
                $this->questions[0]['is_required'] = true;
            }
        }
    }

    public function addOption(int $questionIndex): void
    {
        if (! isset($this->questions[$questionIndex])) {
            return;
        }

        $this->questions[$questionIndex]['options'][] = ['option_text' => ''];
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (
            ! isset($this->questions[$questionIndex]['options'][$optionIndex]) ||
            count($this->questions[$questionIndex]['options']) <= 2
        ) {
            return;
        }

        array_splice($this->questions[$questionIndex]['options'], $optionIndex, 1);
    }

    public function handleQuestionTypeChange(int $questionIndex, string $type): void
    {
        if (! isset($this->questions[$questionIndex])) {
            return;
        }

        try {
            $questionType = QuestionType::from($type);
        } catch (ValueError) {
            return;
        }

        $this->questions[$questionIndex]['type'] = $questionType;

        if ($questionType !== QuestionType::MULTIPLE_CHOICE) {
            $this->questions[$questionIndex]['options'] = [];
        } elseif (empty($this->questions[$questionIndex]['options'])) {
            $this->questions[$questionIndex]['options'] = [
                ['option_text' => ''],
                ['option_text' => ''],
            ];
        }
    }

    /**
     * @throws Throwable
     */
    public function updateSurvey(): void
    {
        $validatedData = $this->validateData();

        DB::transaction(function () use ($validatedData) {
            $this->survey->update($validatedData['survey']);

            $existingQuestions = $this->survey
                ->questions()
                ->with('options')
                ->get()
                ->keyBy('id');

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

                if ($questionData['type'] !== QuestionType::MULTIPLE_CHOICE) {
                    continue;
                }

                $existingOptions = $question->options->keyBy('id');
                $submittedOptions = collect($questionData['options'] ?? []);

                foreach ($submittedOptions as $optionIndex => $optionData) {
                    $option = null;

                    if (is_array($optionData) && isset($optionData['id'])) {
                        $option = $existingOptions->get($optionData['id']);
                    }

                    if ($option) {
                        $option->update([
                            'option_text' => $optionData['option_text'],
                            'order_index' => $optionIndex,
                        ]);

                        continue;
                    }

                    $optionText = is_array($optionData)
                        ? $optionData['option_text'] ?? null
                        : $optionData;

                    if (empty($optionText)) {
                        continue;
                    }

                    $question->options()->create([
                        'option_text' => $optionText,
                        'order_index' => $optionIndex,
                    ]);
                }

                $submittedOptionIds = $submittedOptions->pluck('id')->filter()->all();
                $optionsToDelete = $existingOptions->keys()->diff($submittedOptionIds);

                foreach ($optionsToDelete as $optionId) {
                    $option = $existingOptions->get($optionId);

                    if ($option && $option->answerOptions()->doesntExist()) {
                        $option->delete();
                    }
                }
            }
        });

        $this->success(__('Survey updated'));

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

    /**
     * @throws ValidationException
     */
    protected function validateData(): array
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'closed_at' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Validator::make(['questions' => $this->questions], [
            'questions' => ['required', 'array'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.is_required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.option_text' => ['required_with:questions.*.options', 'string', 'max:255'],
        ])->validate();

        return [
            'survey' => [
                'title' => mb_trim($this->title),
                'description' => $this->description,
                'closed_at' => $this->closed_at ? Carbon::parse($this->closed_at) : null,
                'is_active' => $this->is_active,
            ],
        ];
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.edit')
            ->title(__('Edit survey'));
    }
}
