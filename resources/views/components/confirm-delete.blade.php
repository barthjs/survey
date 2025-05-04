@props([
    'title' => null,
    'subtitle' => null,
    'deleteAction' => 'delete',
])

<x-modal
    :title="$title ?? __('Confirm deletion')"
    :subtitle="$subtitle ?? __('Are you sure you want to do this?')"
    wire:model="confirmDeletionModal"
    class="backdrop-blur"
>
    <x-slot:actions>
        <x-button
            :label="__('Cancel')"
            @click="$wire.set('confirmDeletionModal', false)"
            icon="o-x-circle"
            class="btn-secondary"
        />
        <x-button
            :label="__('Delete')"
            spinner="{{ $deleteAction }}"
            wire:click="{{ $deleteAction }}"
            icon="o-trash"
            class="btn-error"
        />
    </x-slot:actions>
</x-modal>
