<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\Question;
use App\Models\Response;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Throwable;

#[Layout('components.layouts.web')]
class SubmitSurvey extends Component
{
    use Toast, WithFileUploads;

    public Survey $survey;

    public string $title;

    public string $description;

    public array $questions;

    public array $response = [];

    public function mount(string $id): void
    {
        $this->survey = Survey::findOrFail($id);
        $this->title = $this->survey->title;
        $this->description = $this->survey->description ?? '';

        if (
            ($this->survey->closed_at && $this->survey->closed_at->isPast()) ||
            ! $this->survey->is_active
        ) {
            abort(404);
        }

        $this->questions = Question::with(['options' => function ($query) {
            $query->orderBy('order_index');
        }])
            ->whereSurveyId($this->survey->id)
            ->orderBy('order_index')
            ->get(['id', 'question_text', 'type', 'is_required', 'order_index'])
            ->toArray();

        if (empty($this->questions)) {
            abort(404);
        }

        foreach ($this->questions as $question) {
            if ($question['type'] === QuestionType::FILE->name) {
                $this->response[$question['id']] = null;
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function submitSurvey(): void
    {
        $rules = [];

        foreach ($this->questions as $question) {
            $questionId = $question['id'];
            $isRequired = $question['is_required'];

            $baseRule = match ($question['type']) {
                QuestionType::TEXT->name => ['string'],
                QuestionType::MULTIPLE_CHOICE->name => ['array'],
                QuestionType::FILE->name => [
                    'file',
                    'max:10240',
                    'mimetypes:text/plain,application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ],
            };

            if ($isRequired) {
                array_unshift($baseRule, 'required');
            } else {
                array_unshift($baseRule, 'nullable');
            }

            $rules["response.$questionId"] = $baseRule;
        }

        $this->validate($rules);

        DB::transaction(function () {
            $response = Response::create([
                'survey_id' => $this->survey->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'submitted_at' => Carbon::now(),
            ]);

            foreach ($this->questions as $question) {
                if (empty($this->response[$question['id']])) {
                    continue;
                }

                $data = $this->response[$question['id']];

                $answer = Answer::create([
                    'question_id' => $question['id'],
                    'response_id' => $response->id,
                    'answer_text' => $question['type'] === QuestionType::TEXT->name
                        ? $data
                        : null,
                    'file_path' => $question['type'] === QuestionType::FILE->name
                        ? $data->store('surveys/'.$response->id)
                        : null,
                    'original_file_name' => $question['type'] === QuestionType::FILE->name
                        ? $data->getClientOriginalName()
                        : null,
                ]);

                if (is_array($data) && $question['type'] === QuestionType::MULTIPLE_CHOICE->name) {
                    foreach (array_keys($data) as $optionId) {
                        AnswerOption::create([
                            'answer_id' => $answer->id,
                            'question_option_id' => $optionId,
                        ]);
                    }
                }
            }
        });

        $this->redirect(route('surveys.thank-you'));
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.submit')
            ->title(__('Submit survey'));
    }
}
