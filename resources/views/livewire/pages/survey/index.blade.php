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

            @scope('cell_end_date', $survey)
            @if ($survey->end_date)
                <x-badge
                    :value="$survey->end_date"
                    :class="($survey->end_date->isPast() && $survey->is_active ? 'badge-warning' : '')"
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

            @scope('cell_is_public', $survey)
            <x-popover>
                <x-slot:trigger>
                    <x-icon
                        :name="$survey->is_public ? 'o-check-circle' : 'o-x-circle'"
                        :class="$survey->is_public ? 'text-success' : 'text-base-content'"
                    />
                </x-slot:trigger>
                <x-slot:content>
                    {{ $survey->is_public ? __('Public') : __('Private') }}
                </x-slot:content>
            </x-popover>
            @endscope

            @scope('actions', $survey)
            @if(
                (! $survey->end_date || ! $survey->end_date->isPast()) &&
                $survey->is_active
            )
                <x-button
                    icon="o-arrow-top-right-on-square"
                    :link="route('surveys.submit', ['id' => $survey->id ])"
                    external
                    class="btn-sm btn-ghost info"
                />
            @endif
            @if($survey->responses()->count() === 0)
                <x-button
                    icon="o-trash"
                    wire:click="confirmDeletion('{{ $survey->id }}')"
                    class="btn-sm btn-ghost text-error"
                />
            @endif
            @endscope
        </x-table>
        <x-confirm-delete :title="__('Delete survey')"/>
    </x-card>
</div>
