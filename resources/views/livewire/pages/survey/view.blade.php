<div>
    <x-header :title="$survey->title" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-pencil"
                :label="__('Edit survey')"
                :link="route('surveys.edit', ['id' => $survey->id])"
                responsive="true"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-header>
</div>
