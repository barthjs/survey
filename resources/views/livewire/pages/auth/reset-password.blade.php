<x-card
    :title="__('Reset password')"
    :subtitle="__('Please enter your new password below')"
    separator
    shadow
    class="w-full max-w-lg mx-auto"
>
    <x-auth-session-status :status="session('status')" class="text-center"/>

    <x-form wire:submit="resetPassword" novalidate>
        <x-input
            icon="o-at-symbol"
            :label="__('Email address')"
            wire:model="email"
            autofocus
            autocomplete="username"
            required
        />

        <x-password
            :label="__('Password')"
            clearable
            wire:model="password"
            autocomplete="new-password"
            required
        />
        <x-password
            :label="__('Confirm password')"
            clearable
            wire:model="password_confirmation"
            autocomplete="new-password"
            required
        />

        <hr class="mt-3"/>

        <x-button
            :label="__('Reset password')"
            spinner="resetPassword"
            type="submit"
            class="btn-primary"
        />
    </x-form>
</x-card>
