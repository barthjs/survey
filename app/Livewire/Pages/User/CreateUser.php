<?php

declare(strict_types=1);

namespace App\Livewire\Pages\User;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class CreateUser extends Component
{
    use Toast;

    public function render(): Application|Factory|View
    {
        return view('livewire.pages.user.create')
            ->title(__('Create user'));
    }
}
