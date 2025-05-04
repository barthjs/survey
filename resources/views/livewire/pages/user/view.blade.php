<div>
    <x-header>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('users.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-pencil"
                :label="__('Edit user')"
                :link="route('users.edit', ['id' => $user->id])"
                responsive="true"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-header>
</div>
