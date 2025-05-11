<div>
    <x-header :title="$survey->title" :subtitle="$survey->description" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                class="btn-secondary"
            />
            <x-button
                icon="o-pencil"
                :label="__('Edit survey')"
                :link="route('surveys.edit', ['id' => $survey->id])"
                class="btn-primary"
            />
            @if (
                (! $this->survey->closed_at || ! $this->survey->closed_at->isPast()) &&
                $this->survey->is_active
            )
                <x-button
                    icon="o-arrow-top-right-on-square"
                    :label="__('Submit')"
                    :link="route('surveys.submit', ['id' => $survey->id])"
                    external
                    class="btn-info"
                />
            @endif
        </x-slot:actions>
    </x-header>
</div>
