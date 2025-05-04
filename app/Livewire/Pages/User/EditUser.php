<?php

declare(strict_types=1);

namespace App\Livewire\Pages\User;

use App\Traits\ConfirmDeletionModal;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class EditUser extends Component
{
    use ConfirmDeletionModal, Toast;

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.user.edit')
            ->title(__('Edit user'));
    }
}
