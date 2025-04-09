<div>
    <x-header :title="__('Profile')" :subtitle="__('Manage your profile and account settings')" shadow
              separator/>
    <x-toast/>

    <x-card :title="__('Profile')" :subtitle="__('Update your name and email address')">
        <x-form wire:submit="updateProfileInformation" novalidate autocomplete="off">
            <x-input required label="{{ __('Full name') }}" wire:model="name" icon="o-user"/>
            <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol"/>

            <x-button :label="__('Save')" type="submit" class="btn-primary" spinner="updateProfileInformation"/>
        </x-form>
    </x-card>

    <x-card :title="__('Update password')"
            :subtitle="__('Ensure your account is using a long, random password to stay secure')">
        <x-form wire:submit="updatePassword" novalidate autocomplete="off">
            <x-password :label="__('Current password')" wire:model="current_password" autocomplete="current-password"/>
            <x-password :label="__('New password')" wire:model="password" autocomplete="new-password"/>
            <x-password :label="__('Confirm Password')" wire:model="password_confirmation" autocomplete="new-password"/>

            <x-button :label="__('Save')" type="submit" class="btn-primary" spinner="updatePassword"/>
        </x-form>
    </x-card>

    <x-card :title="__('Delete account')" :subtitle="__('Delete your account and all of its resources')">
        <x-modal wire:model="confirmUserDeletionModalIsVisible"
                 :title="__('Are you sure you want to delete your account?')" persistent separator
                 class="backdrop-blur">
            <x-form wire:submit="deleteUser">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                <x-password :label="__('Password')" wire:model="password"/>

                <x-slot:actions>
                    <x-button :label="__('Cancel')" wire:click="closeModalConfirmUserDeletion" icon="o-x-circle"
                              class="btn-secondary"/>
                    <x-button :label="__('Delete account')" type="submit" icon="o-trash" class="btn-error"/>
                </x-slot:actions>
            </x-form>
        </x-modal>

        <x-button :label="__('Delete account')" wire:click="openModalConfirmUserDeletion" icon="o-trash"
                  class="btn-error" spinner/>
    </x-card>
</div>
