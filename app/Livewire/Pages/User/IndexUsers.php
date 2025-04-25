<?php

declare(strict_types=1);

namespace App\Livewire\Pages\User;

use App\Models\User;
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
class IndexUsers extends Component
{
    use Toast, WithPagination;

    #[Session]
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    #[Url]
    public string $search = '';

    public int $perPage = 10;

    public bool $confirmDeletionModalIsVisible = false;

    private function tableHeaders(): array
    {
        return [
            ['key' => 'name', 'label' => __('Name')],
            ['key' => 'email', 'label' => __('Email')],
            ['key' => 'created_at', 'label' => __('Created at'), 'format' => ['date', 'd-m-Y']],
            ['key' => 'updated_at', 'label' => __('Updated at'), 'format' => ['date', 'd-m-Y']],
            ['key' => 'is_active', 'label' => __('Status')],
            ['key' => 'is_admin', 'label' => __('Admin')],
        ];
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn (Builder $query) => $query->where('name', 'like', "%$this->search%")
                ->orWhere('email', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function openModalConfirmDelete(): void
    {
        $this->confirmDeletionModalIsVisible = true;
    }

    public function closeModalConfirmDelete(): void
    {
        $this->confirmDeletionModalIsVisible = false;
    }

    public function delete(string $id): void
    {
        $user = User::find($id);

        if ($user->is_admin) {
            $this->error(__('You cannot delete an admin'));

            return;
        }

        if (auth()->user()->id === $user->id) {
            $this->error(__('Go to the profile in order to delete your account.'));

            return;
        }

        $user->delete();
        $this->closeModalConfirmDelete();
        $this->warning('Deleted user');
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.user.index')
            ->with('headers', $this->tableHeaders())
            ->with('users', $this->users())
            ->title(__('Users'));
    }
}
