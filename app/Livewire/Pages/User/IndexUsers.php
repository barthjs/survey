<?php

declare(strict_types=1);

namespace App\Livewire\Pages\User;

use App\Models\User;
use App\Traits\ConfirmDeletionModal;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
    use ConfirmDeletionModal, Toast, WithPagination;

    #[Session]
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    #[Url]
    public string $search = '';

    public int $perPage = 10;

    public bool $createUserModal = false;

    public bool $editUserModal = false;

    public string $editUserId = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = true;

    public bool $is_admin = false;

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

    public function openCreateUserModal(): void
    {
        $this->reset('name', 'email', 'password', 'password_confirmation', 'is_active', 'is_admin');

        $this->is_active = true;
        $this->is_admin = false;

        $this->createUserModal = true;
    }

    public function createUser(): void
    {
        $this->email = mb_strtolower($this->email);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
            'is_admin' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(User::create($validated)));

        $this->reset('name', 'email', 'password', 'password_confirmation', 'is_active', 'is_admin');

        $this->createUserModal = false;

        $this->success(__('User created successfully'));
    }

    public function editUser(string $id): void
    {
        $this->reset('name', 'email', 'password', 'password_confirmation', 'is_active', 'is_admin');

        $user = User::findOrFail($id);
        $this->editUserId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = $user->is_active;
        $this->is_admin = $user->is_admin;

        $this->editUserModal = true;
    }

    public function updateUser(): void
    {
        $user = User::findOrFail($this->editUserId);

        $this->email = mb_strtolower($this->email);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'is_active' => ['boolean'],
            'is_admin' => ['boolean'],
        ];

        if (! empty($this->password)) {
            $rules['password'] = ['string', Password::defaults(), 'confirmed'];
        }

        $validated = $this->validate($rules);

        if (! empty($this->password)) {
            $validated['password'] = Hash::make($this->password);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->editUserModal = false;

        $this->success(__('User updated successfully'));
    }

    public function delete(): void
    {
        $user = User::findOrFail($this->deletionId);

        if ($user->is_admin) {
            $this->closeConfirmDeletionModal();
            $this->error(__('You cannot delete an admin'));

            return;
        }

        if (auth()->user()->id === $user->id) {
            abort(403);
        }

        $user->delete();

        $this->closeConfirmDeletionModal();

        $this->warning(__('Deleted user'));
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.user.index')
            ->with('headers', $this->tableHeaders())
            ->with('users', $this->users())
            ->title(__('Users'));
    }
}
