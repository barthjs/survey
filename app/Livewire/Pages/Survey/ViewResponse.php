<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Answer;
use App\Models\QuestionOption;
use App\Models\Response;
use App\Traits\ConfirmDeletionModal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app')]
class ViewResponse extends Component
{
    use ConfirmDeletionModal, Toast;

    public Response $response;

    public array $answers = [];

    public function mount(string $id): void
    {
        $this->response = Response::with([
            'survey',
            'answers.question',
            'answers.question.options',
        ])->findOrFail($id);

        if (auth()->user()->cannot('view', $this->response)) {
            abort(403);
        }

        $this->answers = $this->response->answers->map(fn (Answer $answer) => [
            'id' => $answer->id,
            'question_id' => $answer->question_id,
            'question_type' => $answer->question->type,
            'question_text' => $answer->question->question_text,
            'question_order_index' => $answer->question->order_index,
            'answer_text' => $answer->answer_text,
            'original_file_name' => $answer->original_file_name,
            'options' => $answer->question->options->map(fn (QuestionOption $option) => [
                'option_text' => $option->option_text,
            ])->toArray(),
        ])->toArray();
    }

    public function deleteResponse(): void
    {
        if (auth()->user()->cannot('delete', $this->response)) {
            abort(403);
        }

        $this->response->delete();
        $this->closeConfirmDeletionModal();
        $this->warning(__('Deleted response'));

        $this->redirect(route('surveys.view', ['id' => $this->response->survey_id]), navigate: true);
    }

    public function deleteAnswer(): void
    {
        $answer = $this->response->answers->firstWhere('id', $this->deletionId);
        if (! $answer) {
            abort(404);
        }

        $answer->delete();

        if ($this->response->answers()->count() === 0) {
            $this->redirect(route('surveys.view', ['id' => $this->response->survey_id]), navigate: true);

            return;
        }

        $this->closeConfirmDeletionModal();
        $this->success(__('Deleted answer'));

        $this->mount($this->response->id);
    }

    public function download(string $id): ?BinaryFileResponse
    {
        $answer = $this->response->answers->firstWhere('id', $id);
        if (! $answer) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($answer->file_path)) {
            $this->error(__('File has been deleted'));

            return null;
        }

        return response()->download(
            Storage::disk('local')->path($answer->file_path),
            $this->response->submitted_at->format('Y-m-d_H-i-s').'_'.$answer->original_file_name,
        );
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.response')
            ->title(__('View answer'));
    }
}
