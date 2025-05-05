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
            icon="o-x-circle"
            :label="__('Cancel')"
            x-on:click="$wire.confirmDeletionModal = false"
            class="btn-secondary"
        />
        <x-button
            icon="o-trash"
            :label="__('Delete')"
            spinner="{{ $deleteAction }}"
            wire:click="{{ $deleteAction }}"
            class="btn-error"
        />
    </x-slot:actions>
</x-modal>
