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
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Storage;
use Throwable;

#[Layout('components.layouts.public')]
class SubmitSurvey extends Component
{
    public const array ALLOWED_MIME_TYPES = [
        'text/plain',
        'text/markdown',
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation',
    ];

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
            $this->survey->end_date?->isPast()
            && $this->survey->is_active
            && is_null($this->survey->auto_closed_at)
        ) {
            $this->survey->is_active = false;
            $this->survey->auto_closed_at = now();
            $this->survey->save();

            abort(404);
        }

        if (! $this->survey->is_active) {
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
                QuestionType::TEXT->name => ['string', 'max:255'],

                QuestionType::MULTIPLE_CHOICE->name => [
                    'array',
                    function (string $attribute, $value, Closure $fail) use ($question) {
                        $validOptionIds = collect($question['options'] ?? [])->pluck('id')->toArray();
                        foreach (array_keys($value) as $optionId) {
                            if (! in_array($optionId, $validOptionIds, true)) {
                                $fail(__('Invalid option selected'));
                            }
                        }
                    },
                ],

                QuestionType::FILE->name => [
                    'file',
                    'max:10240',
                    'mimetypes:'.implode(',', self::ALLOWED_MIME_TYPES),
                    function (string $attribute, $value, Closure $fail) {
                        if (! $value instanceof TemporaryUploadedFile || ! $value->isValid()) {
                            $fail(__('Invalid file upload'));

                            return;
                        }

                        if (mb_strlen($value->getClientOriginalName()) > 255) {
                            $fail(__('The file name must not exceed 255 characters.'));
                        }
                    },
                ],
            };

            if ($isRequired) {
                array_unshift($baseRule, 'required');
            } else {
                array_unshift($baseRule, 'nullable');
            }

            $rules["response.$questionId"] = $baseRule;
        }

        try {
            $this->validate($rules);
        } catch (ValidationException $e) {
            $failedFields = array_keys($e->validator->failed());

            foreach ($failedFields as $field) {
                $questionId = str_replace('response.', '', $field);
                $file = $this->response[$questionId] ?? null;

                if ($file instanceof TemporaryUploadedFile) {
                    $path = 'livewire-tmp/'.$file->getFilename();

                    if (Storage::disk('local')->exists($path)) {
                        Log::warning('File upload violated validation.', [
                            'question_id' => $questionId,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size_kb' => round($file->getSize() / 1024, 2),
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'session_id' => session()->getId(),
                        ]);

                        Storage::disk('local')->delete($path);
                    }
                }

                $this->response[$questionId] = null;
            }

            throw $e;
        }

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

                /** @var TemporaryUploadedFile|string|null $data */
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
            ->title($this->title);
    }
}
