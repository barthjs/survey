<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
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

    public bool $is_public = false;

    public bool $is_active = true;

    public ?string $end_date = null;

    public array $questions;

    public function mount(): void
    {
        $this->questions[] = [
            'question_text' => '',
            'type' => QuestionType::TEXT,
            'is_required' => true,
            'options' => [],
        ];
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        $validatedSurvey = $this->validateData();

        try {
            Question::validateQuestions($this->questions);
        } catch (ValidationException $e) {
            $this->dispatch('validationErrors', $e->errors());

            return;
        }

        DB::transaction(function () use ($validatedSurvey, &$survey) {
            $survey = Survey::create($validatedSurvey);
            $this->createSurveyQuestions($survey);
        });

        $this->success(__('Survey created'));

        $this->reset();

        $this->redirect(route('surveys.view', $survey->id), navigate: true);
    }

    private function createSurveyQuestions(Survey $survey): void
    {
        foreach ($this->questions as $questionIndex => $questionData) {
            $question = $survey->questions()->create([
                'question_text' => $questionData['question_text'],
                'type' => $questionData['type'],
                'is_required' => $questionData['is_required'],
                'order_index' => $questionIndex,
            ]);

            if ($questionData['type'] === QuestionType::MULTIPLE_CHOICE->name) {
                foreach ($questionData['options'] as $optionIndex => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'order_index' => $optionIndex,
                    ]);
                }
            }
        }
    }

    /**
     * @throws ValidationException
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
            'user_id' => auth()->id(),
        ];
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.create')
            ->title(__('Create survey'));
    }
}
