<div>
    <x-header :title="$survey->title" separator>
        <x-slot:actions>
            <x-button :label="__('Edit survey')" icon="o-pencil" :link="route('surveys.edit', ['id' => $survey->id])"
                      class="btn-sm btn-primary" responsive="true"/>
        </x-slot:actions>
    </x-header>
</div>
