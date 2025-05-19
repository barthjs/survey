<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app')]
class ViewSurvey extends Component
{
    use WithPagination;

    public Survey $survey;

    public Collection $questions;

    public function mount(string $id): void
    {
        $this->survey = Survey::findOrFail($id);

        if (auth()->user()->cannot('view', $this->survey)) {
            abort(403);
        }

        $this->questions = Question::query()
            ->with(['answers.response', 'answers.selectedOptions.option', 'options'])
            ->where('survey_id', $this->survey->id)
            ->orderBy('order_index')
            ->get();
    }

    public function getChartData(Question $question): array
    {
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

    public function download(string $id): BinaryFileResponse
    {
        $answer = Answer::findOrFail($id);

        return response()->download(
            Storage::disk('local')->path($answer->file_path),
            $answer->response->submitted_at->format('Y-m-d_H-i-s').'_'.$answer->original_file_name,
        );
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.view')
            ->title(__('View survey'));
    }
}
