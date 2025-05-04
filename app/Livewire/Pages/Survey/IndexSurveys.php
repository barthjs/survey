<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Survey;
use App\Traits\ConfirmDeletionModal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class IndexSurveys extends Component
{
    use ConfirmDeletionModal, Toast, WithPagination;

    #[Session]
    public array $sortBy = ['column' => 'title', 'direction' => 'asc'];

    #[Url]
    public string $search_url = '';

    public int $perPage = 10;

    private function tableHeaders(): array
    {
        return [
            ['key' => 'title', 'label' => __('Title')],
            ['key' => 'created_at', 'label' => __('Created at'), 'format' => ['date', 'd-m-Y']],
            ['key' => 'closed_at', 'label' => __('Closed at')],
            ['key' => 'is_active', 'label' => __('Status')],
        ];
    }

    public function surveys(): LengthAwarePaginator
    {
        return Survey::query()
            ->where('user_id', auth()->id())
            ->when($this->search_url, fn (Builder $query) => $query->where('title', 'like', "%$this->search_url%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function delete(): void
    {
        $survey = Survey::findOrFail($this->deletionId);

        if (auth()->user()->cannot('delete', $survey)) {
            abort(403);
        }

        if ($survey->responses()->count() > 0) {
            abort(403);
        }

        $survey->delete();
        $this->closeConfirmDeletionModal();
        $this->warning(__('Deleted survey'));
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.survey.index')
            ->with('headers', $this->tableHeaders())
            ->with('surveys', $this->surveys())
            ->title(__('Surveys'));
    }
}
