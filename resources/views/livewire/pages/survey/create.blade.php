<div>
    <x-header :title="__('Create survey')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-check"
                :label="__('Create survey')"
                responsive
                x-on:click="$dispatch('save-survey')"
                class="btn-success"
            />
        </x-slot:actions>
    </x-header>

    <x-manage-survey-form :questions="$questions"/>
</div>
