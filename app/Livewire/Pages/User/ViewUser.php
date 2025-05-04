<?php

declare(strict_types=1);

namespace App\Livewire\Pages\User;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewUser extends Component
{
    public User $user;

    public function mount(string $id): void
    {
        $this->user = User::findOrFail($id);
    }

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.user.view')
            ->title(__('View user'));
    }
}
