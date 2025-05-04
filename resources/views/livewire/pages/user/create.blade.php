<div>
    <x-header :title="__('Create user')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('users.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-check"
                :label="__('Create user')"
                responsive
                spinner="createUser"
                wire:click="createUser"
                class="btn-success"
            />
        </x-slot:actions>
    </x-header>
</div>
