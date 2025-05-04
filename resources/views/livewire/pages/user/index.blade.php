<div>
    <x-header :title="__('Users')" separator>
        <x-slot:actions>
            <x-button
                :label="__('Create user')"
                :link="route('users.create')"
                icon="o-plus"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-input icon="o-magnifying-glass" clearable wire:model.live.debounce="search" class="max-w-md"/>

        <x-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            :link="route('users.view', ['id' => '[id]'])"
            per-page="perPage"
            striped
            with-pagination
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
                <x-button
                    icon="o-trash"
                    wire:click="confirmDeletion('{{ $user->id }}')"
                    class="btn-sm btn-ghost text-error"
                />
                <x-confirm-delete :title="__('Delete user')"/>
            @endif
            @endscope
        </x-table>
    </x-card>
</div>
