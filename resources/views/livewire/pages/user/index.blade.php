<div>
    <x-header :title="__('Users')" separator>
    </x-header>

    <x-card>
        <x-input wire:model.live.debounce="search" icon="o-magnifying-glass" clearable class="max-w-md"/>

        <x-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            striped
            with-pagination
            per-page="perPage"
        >
            <x-slot:empty>
                <x-icon name="o-information-circle" :label="__('No users found')"/>
            </x-slot:empty>

            @scope('cell_is_active', $survey)
            <x-popover>
                <x-slot:trigger>
                    <x-icon
                        :name="$survey->is_active ? 'o-check-circle' : 'o-x-circle'"
                        :class="$survey->is_active ? 'text-success' : 'text-error'"
                    />
                </x-slot:trigger>
                <x-slot:content>
                    {{ $survey->is_active ? __('Active') : __('Inactive') }}
                </x-slot:content>
            </x-popover>
            @endscope

            @scope('cell_is_admin', $survey)
            <x-icon
                :name="$survey->is_admin ? 'o-check-circle' : 'o-x-circle'"
                :class="$survey->is_admin ? 'text-success' : 'text-base-content'"
            />
            @endscope

            @scope('actions', $user)
            @if(auth()->user()->id !== $user->id)
                <x-button icon="o-trash" wire:click="openModalConfirmDelete" class="btn-sm btn-ghost text-error"/>

                <x-modal wire:model="confirmDeletionModalIsVisible" :title="__('Delete User')"
                         :subtitle="__('Are you sure you would like to do this?')">

                    <x-slot:actions>
                        <x-button :label="__('Cancel')" wire:click="closeModalConfirmDelete"
                                  icon="o-x-circle" class="btn-secondary"/>
                        <x-button :label="__('Delete')" wire:click="delete('{{ $user->id }}')" icon="o-trash"
                                  class="btn-error"/>
                    </x-slot:actions>
                </x-modal>
            @endif
            @endscope
        </x-table>
    </x-card>
</div>
