<div>
    <x-header>
        <x-slot:actions>
            <x-button
                icon="o-plus"
                :label="__('Create survey')"
                :link="route('surveys.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-input
            icon="o-magnifying-glass"
            :placeholder="__('Enter survey title...')"
            clearable
            wire:model.live.debounce="search"
            class="max-w-md"
        />

        <x-table
            :headers="$headers"
            :rows="$surveys"
            :sort-by="$sortBy"
            :link="route('surveys.view', ['id' => '[id]'])"
            per-page="perPage"
            striped
            with-pagination
        >
            <x-slot:empty>
                <x-icon name="o-information-circle" :label="__('No surveys found')"/>
            </x-slot:empty>

            @scope('cell_closed_at', $survey)
            @if ($survey->closed_at)
                <x-badge
                    :value="$survey->closed_at"
                    :class="($survey->closed_at->isPast() && $survey->is_active ? 'badge-warning' : '')"
                />
            @else
                <x-badge :value="__('No end date')" class="text-base-content/50"/>
            @endif
            @endscope

            @scope('cell_is_active', $survey)
            <x-popover>
                <x-slot:trigger>
                    <x-icon
                        :name="$survey->is_active ? 'o-check-circle' : 'o-x-circle'"
                        :class="$survey->is_active ? 'text-success' : 'text-base-content'"
                    />
                </x-slot:trigger>
                <x-slot:content>
                    {{ $survey->is_active ? __('Open') : __('Closed') }}
                </x-slot:content>
            </x-popover>
            @endscope

            @scope('actions', $survey)
            <x-button
                icon="o-arrow-top-right-on-square"
                :link="route('surveys.submit', ['id' => $survey->id ])"
                external
                class="btn-sm btn-ghost info"
            />
            @if($survey->responses()->count() === 0)
                <x-button
                    icon="o-trash"
                    wire:click="confirmDeletion('{{ $survey->id }}')"
                    class="btn-sm btn-ghost text-error"
                />
                <x-confirm-delete :title="__('Delete survey')"/>
            @endif
            @endscope
        </x-table>
    </x-card>
</div>
