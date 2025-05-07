<x-modal :title="__('Edit user')" persistent wire:model="editUserModal" class="backdrop-blur">
    <div wire:dirty class="mb-4 2xl:text-warning">{{ __('Unsaved changes!') }}</div>

    <div x-data="{ password: @entangle('password') }">
        <x-form wire:submit="updateUser" novalidate autocomplete="off">
            <x-input icon="o-user" :label="__('Full name')" wire:model="name" required/>
            <x-input icon="o-at-symbol" :label="__('Email address')" wire:model="email" required/>

            <x-password :label="__('Password')" clearable wire:model="password"/>
            <template x-if="password">
                <x-password :label=" __('Confirm Password')" clearable wire:model="password_confirmation" required/>
            </template>

            <x-checkbox :label="__('Admin')" wire:model="is_admin"/>
            <x-checkbox :label="__('Active')" wire:model="is_active"/>

            <x-slot:actions>
                <x-button
                    icon="o-x-circle"
                    :label="__('Cancel')"
                    x-on:click="$wire.editUserModal = false"
                    responsive
                    class="btn-secondary"
                />
                <x-button
                    icon="o-check"
                    :label="__('Save')"
                    responsive
                    spinner="updateUser"
                    wire:click="updateUser"
                    class="btn-success"
                />
            </x-slot:actions>
        </x-form>
    </div>
</x-modal>
