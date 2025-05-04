<div>
    <x-header :title="__('Edit user')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('users.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-trash"
                :label="__('Delete user')"
                responsive
                wire:click="confirmDeletion"
                class="btn-error"
            />
            <x-button
                icon="o-check"
                :label="__('Update user')"
                responsive
                spinner="updateSurvey"
                wire:click="updateUser"
                class="btn-success"
            />
            <x-confirm-delete :title="__('Delete user')" deleteAction="deleteUser"/>
        </x-slot:actions>
    </x-header>
</div>
