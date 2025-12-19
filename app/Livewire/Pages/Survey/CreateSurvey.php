<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Survey;
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
final class CreateSurvey extends Component
{
    use Toast;

    public string $title = '';

    public ?string $description = null;

    public bool $is_public = false;

    public bool $is_active = true;

    public ?string $end_date = null;

    /**
     * @var array<int, array{
     *     question_text: string,
     *     type: string,
     *     is_required: bool,
     *     options: array<int, array{option_text: string}>
     * }>
     */
    public array $questions;

    public function mount(): void
    {
        $this->questions[] = [
            'question_text' => '',
            'type' => QuestionType::TEXT->value,
            'is_required' => true,
            'options' => [],
        ];
    }

    public function save(): void
    {
        $validatedSurvey = $this->validateData();

        try {
            Question::validateQuestions($this->questions);
        } catch (ValidationException $e) {
            $this->dispatch('validationErrors', $e->errors());

            return;
        }

        $survey = DB::transaction(function () use ($validatedSurvey, &$survey): Survey {
            $validatedSurvey['user_id'] = auth()->id();
            $survey = Survey::create($validatedSurvey);
            $this->createSurveyQuestions($survey);

            return $survey;
        });

        $this->success(__('Survey created'));

        $this->redirect(route('surveys.view', $survey->id), navigate: true);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.create')
            ->title(__('Create survey'));
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

            if ($questionData['type'] === QuestionType::MULTIPLE_CHOICE->value) {
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
