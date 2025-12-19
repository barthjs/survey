<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Survey;

use App\Models\Survey;
use App\Traits\ConfirmDeletionModal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
final class IndexSurveys extends Component
{
    use ConfirmDeletionModal, Toast, WithPagination;

    /** @var array<string, string> */
    #[Url]
    public array $sortBy = ['column' => 'title', 'direction' => 'asc'];

    #[Url]
    public string $search = '';

    #[Url]
    public int $perPage = 10;

    /**
     * @return LengthAwarePaginator<int, Survey>
     */
    #[Computed]
    public function surveys(): LengthAwarePaginator
    {
        $allowedPerPage = [10, 20, 50, 100];

        return Survey::query()
            ->where('user_id', auth()->id())
            ->when($this->search, fn (Builder $query) => $query->where('title', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(in_array($this->perPage, $allowedPerPage) ? $this->perPage : $allowedPerPage[0]);
    }

    public function delete(): void
    {
        $survey = Survey::findOrFail($this->deletionId);
        if (auth()->user()->cannot('delete', $survey)) {
            return;
        }

        if ($survey->responses()->count() > 0) {
            return;
        }

        $survey->delete();

        $this->closeConfirmDeletionModal();
        $this->warning(__('Deleted survey'));
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.survey.index')
            ->with('headers', [
                ['key' => 'title', 'label' => __('Title')],
                ['key' => 'created_at', 'label' => __('Created at'), 'format' => ['date', 'Y-m-d H:i:s']],
                ['key' => 'end_date', 'label' => __('End date'), 'format' => ['date', 'Y-m-d H:i:s']],
                ['key' => 'is_active', 'label' => __('Status')],
                ['key' => 'is_public', 'label' => __('Public')],
            ])
            ->with('surveys', $this->surveys());
    }
}
