<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\Survey;
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

#[Layout('components.layouts.app')]
class CreateSurvey extends Component
{
    use Toast;

    public string $title = '';

    public ?string $description = null;

    public ?string $closed_at = null;

    public array $questions = [];

    public array $questionTypes;

    public function mount(): void
    {
        $this->questionTypes = collect(QuestionType::cases())->map(fn ($type) => [
            'id' => $type->value,
            'name' => $type->label(),
        ])->toArray();
    }

    /**
     * @throws Throwable
     */
    public function createSurvey(): void
    {
        $validatedSurvey = $this->validateSurveyData();

        try {
            $this->validateQuestions();
        } catch (ValidationException $e) {
            $this->dispatch('validationErrors', $e->errors());

            return;
        }

        DB::transaction(function () use ($validatedSurvey, &$survey) {
            $survey = Survey::create($validatedSurvey);
            $this->createSurveyQuestions($survey);
        });

        $this->success(__('Survey created'));

        $this->reset('title', 'description', 'closed_at');

        $this->redirect(route('surveys.view', $survey->id), navigate: true);
    }

    /**
     * @throws ValidationException
     */
    protected function validateSurveyData(): array
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'closed_at' => ['nullable', 'string'],
        ]);

        return [
            'title' => mb_trim($this->title),
            'description' => $this->description,
            'user_id' => auth()->id(),
            'closed_at' => $this->closed_at ? Carbon::parse($this->closed_at) : null,
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function validateQuestions(): void
    {
        Validator::make(['questions' => $this->questions], [
            'questions' => ['required', 'array'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.is_required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'required', 'max:255'],
        ])->validate();
    }

    protected function createSurveyQuestions(Survey $survey): void
    {
        foreach ($this->questions as $questionIndex => $questionData) {
            $question = $survey->questions()->create([
                'question_text' => $questionData['question_text'],
                'type' => $questionData['type'],
                'is_required' => $questionData['is_required'],
                'order_index' => $questionIndex,
            ]);

            if (
                $questionData['type'] === QuestionType::MULTIPLE_CHOICE->name &&
                ! empty($questionData['options'])
            ) {
                foreach ($questionData['options'] as $optionIndex => $optionText) {
                    if (! empty($optionText)) {
                        $question->options()->create([
                            'option_text' => $optionText,
                            'order_index' => $optionIndex,
                        ]);
                    }
                }
            }
        }
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.create')
            ->title(__('Create survey'));
    }
}
