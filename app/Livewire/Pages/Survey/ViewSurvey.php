<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Survey;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewSurvey extends Component
{
    public Survey $survey;

    public function mount(string $id): void
    {
        $this->survey = Survey::findOrFail($id);

        if (auth()->user()->cannot('view', $this->survey)) {
            abort(403);
        }
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.view')
            ->title(__('View survey'));
    }
}
