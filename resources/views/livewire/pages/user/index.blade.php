<div>
    <x-header :title="__('Users')" separator>
        <x-slot:actions>
            <x-button
                icon="o-plus"
                :label="__('Create user')"
                wire:click="openCreateUserModal"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-header>

    <x-user.create/>
    <x-user.edit/>

    <x-card>
        <x-input icon="o-magnifying-glass" clearable wire:model.live.debounce="search" class="max-w-md"/>

        <x-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            per-page="perPage"
            striped
            with-pagination
        >
            <x-slot:empty>
                <x-icon name="o-information-circle" :label="__('No users found')"/>
            </x-slot:empty>

            @scope('cell_email', $user)
            <a href="mailto:{{ $user->email }}" class="link">{{ $user->email }}</a>
            @endscope

            @scope('cell_is_active', $user)
            <x-popover>
                <x-slot:trigger>
                    <x-icon
                        :name="$user->is_active ? 'o-check-circle' : 'o-x-circle'"
                        :class="$user->is_active ? 'text-success' : 'text-error'"
                    />
                </x-slot:trigger>
                <x-slot:content>
                    {{ $user->is_active ? __('Active') : __('Inactive') }}
                </x-slot:content>
            </x-popover>
            @endscope

            @scope('cell_is_admin', $user)
            <x-icon
                :name="$user->is_admin ? 'o-check-circle' : 'o-x-circle'"
                :class="$user->is_admin ? 'text-success' : 'text-base-content'"
            />
            @endscope

            @scope('actions', $user)
            @if(auth()->user()->id !== $user->id)
                <div class="flex">
                    <x-button
                        icon="o-pencil"
                        wire:click="editUser('{{ $user->id }}')"
                        class="btn-sm btn-ghost text-primary p-1"
                    />
                    <x-button
                        icon="o-trash"
                        wire:click="confirmDeletion('{{ $user->id }}')"
                        class="btn-sm btn-ghost text-error"
                    />
                </div>
                <x-confirm-delete :title="__('Delete user')"/>
            @endif
            @endscope
        </x-table>
    </x-card>
</div>
