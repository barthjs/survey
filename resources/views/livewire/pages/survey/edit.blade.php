<div>
    <x-header :title="__('Edit survey')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.view', ['id' => $survey->id])"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-trash"
                :label="__('Delete')"
                responsive
                wire:click="confirmDeletion"
                class="btn-error"
            />
            <x-button
                icon="o-bookmark-square"
                :label="__('Save')"
                responsive
                x-on:click="$dispatch('save-survey')"
                class="btn-success"
            />
            <x-confirm-delete :title="__('Delete survey')" deleteAction="deleteSurvey"/>
        </x-slot:actions>
    </x-header>

    <x-manage-survey-form :questions="$questions"/>
</div>
