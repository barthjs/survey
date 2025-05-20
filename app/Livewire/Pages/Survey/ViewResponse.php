<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

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

    public function mount(string $id): void
    {
        $this->response = Response::with([
            'survey',
            'answers.question.options',
            'answers.selectedOptions.option',
        ])->findOrFail($id);

        if (auth()->user()->cannot('view', $this->response->survey)) {
            abort(403);
        }
    }

    public function deleteResponse(): void
    {
        $this->response->delete();
        $this->closeConfirmDeletionModal();
        $this->warning(__('Deleted response'));

        $this->redirect(route('surveys.view', ['id' => $this->response->survey_id]), navigate: true);
    }

    public function download(string $id): BinaryFileResponse
    {
        $answer = $this->response->answers->firstWhere('id', $id);

        if (! $answer) {
            abort(404);
        }

        return response()->download(
            Storage::disk('local')->path($answer->file_path),
            $answer->response->submitted_at->format('Y-m-d_H-i-s').'_'.$answer->original_file_name,
        );
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.response')
            ->title('View Response');
    }
}
