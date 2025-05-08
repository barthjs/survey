<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.web')]
class SubmitSurvey extends Component
{
    public Survey $survey;

    public Collection $questions;

    public array $response = [];

    public function mount(string $id): void
    {
        $this->survey = Survey::select('id', 'title', 'description', 'closed_at', 'is_active')
            ->findOrFail($id);

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
            ->get(['id', 'survey_id', 'question_text', 'type', 'is_required', 'order_index']);

        if ($this->questions->isEmpty()) {
            abort(404);
        }
    }

    public function submitSurvey() {}

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.submit')
            ->title(__('Submit survey'));
    }
}
