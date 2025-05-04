<div class="mx-auto max-w-4xl space-y-8">
    <x-header :title="__('Profile')" :subtitle="__('Manage your profile and account settings')" separator/>

    <x-card :title="__('Profile')" :subtitle="__('Update your name and email address')">
        <x-form wire:submit="updateProfileInformation" novalidate autocomplete="off">
            <x-input icon="o-user" :label="__('Full name')" wire:model="name" required/>
            <x-input icon="o-at-symbol" :label="__('Email address')" wire:model="email" required/>

            <x-slot:actions class="justify-start">
                <x-button :label="__('Save')" spinner="updateProfileInformation" type="submit" class="btn-primary"/>
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card
        :title="__('Update password')"
        :subtitle="__('Ensure your account is using a long, random password to stay secure')"
    >
        <x-form wire:submit="updatePassword" novalidate autocomplete="off">
            <x-password :label="__('Current password')" wire:model="current_password" autocomplete="current-password"/>
            <x-password :label="__('New password')" wire:model="password" autocomplete="new-password"/>
            <x-password :label="__('Confirm Password')" wire:model="password_confirmation" autocomplete="new-password"/>

            <x-slot:actions class="justify-start">
                <x-button :label="__('Save')" spinner="updatePassword" type="submit" class="btn-primary"/>
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card :title="__('Delete account')" :subtitle="__('Delete your account and all of its resources')">
        <x-modal
            :title="__('Are you sure you want to delete your account?')"
            wire:model="confirmUserDeletionModalIsVisible"
            class="backdrop-blur"
        >
            <x-form wire:submit="deleteUser">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                <x-password :label="__('Password')" wire:model="password"/>

                <x-slot:actions class="justify-start">
                    <x-button
                        icon="o-x-circle"
                        :label="__('Cancel')"
                        wire:click="closeModalConfirmUserDeletion"
                        class="btn-secondary"
                    />
                    <x-button
                        icon="o-trash"
                        :label="__('Delete account')"
                        type="submit"
                        class="btn-error"
                    />
                </x-slot:actions>
            </x-form>
        </x-modal>

        <x-button
            icon="o-trash"
            :label="__('Delete account')"
            wire:click="openModalConfirmUserDeletion"
            class="btn-error"
        />
    </x-card>
</div>
