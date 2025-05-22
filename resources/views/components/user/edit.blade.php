<x-modal :title="__('Edit user')" persistent wire:model="editUserModal" class="backdrop-blur">
    <div>
        <x-form wire:submit="updateUser" novalidate autocomplete="off">
            <x-alert
                wire:dirty
                class="alert-warning"
            >
                <div class="flex items-start gap-2">
                    <x-icon name="o-exclamation-triangle"/>
                    <div>{{ __('Unsaved changes!') }}</div>
                </div>
            </x-alert>

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
