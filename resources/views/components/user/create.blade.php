<x-modal :title="__('Create user')" persistent wire:model="createUserModal" class="backdrop-blur">
    <x-form wire:submit="createUser" novalidate autocomplete="off">
        <x-input icon="o-user" :label="__('Full name')" wire:model="name" required/>
        <x-input icon="o-at-symbol" :label="__('Email address')" wire:model="email" required/>

        <x-password :label="__('Password')" clearable wire:model="password" required/>
        <x-password :label=" __('Confirm password')" clearable wire:model="password_confirmation" required/>

        <x-checkbox :label="__('Admin')" wire:model="is_admin"/>
        <x-checkbox :label="__('Active')" wire:model="is_active"/>

        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                x-on:click="$wire.createUserModal = false"
                class="btn-secondary"
            />
            <x-button
                icon="o-check"
                :label="__('Create User')"
                spinner="createUser"
                wire:click="createUser"
                class="btn-success"
            />
        </x-slot:actions>
    </x-form>
</x-modal>
