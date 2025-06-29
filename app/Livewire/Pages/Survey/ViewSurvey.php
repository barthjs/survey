<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Survey;
use App\Notifications\SurveyLinkNotification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViewSurvey extends Component
{
    use Toast, WithPagination;

    public Survey $survey;

    public bool $sendEmailModal = false;

    #[Validate('required|string|email')]
    public string $email = '';

    public array $questions = [];

    public function mount(string $id): void
    {
        $this->survey = Survey::findOrFail($id);

        if (! $this->survey->is_public) {
            if (! auth()->check() || auth()->user()->cannot('view', $this->survey)) {
                abort(403);
            }
        }

        $this->questions = Question::query()
            ->with(['answers', 'answers.response', 'answers.selectedOptions.option', 'options'])
            ->where('survey_id', $this->survey->id)
            ->orderBy('order_index')
            ->get()
            ->map(fn (Question $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'is_required' => $question->is_required,
                'answers' => $question->answers
                    ->sortByDesc(fn (Answer $answer) => $answer->response->submitted_at)
                    ->map(fn (Answer $answer) => [
                        'id' => $answer->id,
                        'answer_text' => $answer->answer_text,
                        'file_path' => $answer->file_path,
                        'original_file_name' => $answer->original_file_name,
                        'response' => [
                            'id' => $answer->response->id,
                            'submitted_at' => $answer->response->submitted_at,
                        ],
                    ])->toArray(),
            ])->toArray();
    }

    public function getChartData(string $id): array
    {
        $question = Question::findOrFail($id);
        if (! $question->survey->is_public) {
            if (! auth()->check() || auth()->user()->cannot('view', $question->survey)) {
                abort(403);
            }
        }

        $options = $question->options;

        $labels = $options->pluck('option_text')->toArray();

        $data = [];
        foreach ($options as $option) {
            $data[] = $option->answerOptions()->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Responses'),
                    'data' => $data,
                ],
            ],
        ];
    }

    public function sendEmail(): void
    {
        $this->validate();

        $cacheKey = 'link_sent_'.$this->survey->id.'_'.$this->email;

        if (Cache::has($cacheKey)) {
            $this->reset('email');
            throw ValidationException::withMessages([
                'email' => __('You can only send one survey link per email every 24 hours'),
            ]);
        }

        Notification::route('mail', $this->email)->notify((new SurveyLinkNotification($this->survey->id, $this->email))->locale(app()->getLocale()));

        Cache::put($cacheKey, true, now()->addHours(24));

        $this->sendEmailModal = false;

        $this->reset('email');

        $this->success(__('Survey link has been sent successfully'));
    }

    public function download(string $id): ?BinaryFileResponse
    {
        $answer = Answer::findOrFail($id);
        if (! $answer->question->survey->is_public) {
            if (! auth()->check() || auth()->user()->cannot('view', $answer->question->survey)) {
                abort(403);
            }
        }

        if (! Storage::disk('local')->exists($answer->file_path)) {
            $this->error(__('File has been deleted'));

            return null;
        }

        return response()->download(
            Storage::disk('local')->path($answer->file_path),
            $answer->response->submitted_at->format('Y-m-d_H-i-s').'_'.$answer->original_file_name,
        );
    }

    public function render(): Application|Factory|View
    {
        $layout = $this->survey->is_public && ! auth()->check()
            ? 'components.layouts.public'
            : 'components.layouts.app';

        return view('livewire.pages.survey.view')
            ->layout($layout)
            ->title(__('View survey'));
    }
}
