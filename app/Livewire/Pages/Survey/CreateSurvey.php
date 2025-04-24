<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateSurvey extends Component
{
    public function mount(): void {}

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.create')
            ->title(__('Create survey'));
    }
}
