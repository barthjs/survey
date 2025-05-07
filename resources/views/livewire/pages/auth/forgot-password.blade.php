<x-card
    :title="__('Forgot password')"
    :subtitle="__('Enter your email to receive a password reset link')"
    separator
    shadow
    class="w-full max-w-lg mx-auto"
>
    <x-auth-session-status :status="session('status')" class="text-center"/>

    <x-form wire:submit="sendPasswordResetLink" novalidate>
        <x-input
            icon="o-at-symbol"
            :label="__('Email address')"
            wire:model="email"
            autofocus
            autocomplete="username"
            required
        />

        <hr class="mt-3"/>

        <div class="w-full flex justify-center">
            <x-button
                icon="o-envelope"
                :label="__('Email password reset link')"
                spinner="sendPasswordResetLink"
                type="submit"
                class="btn-primary"
            />
        </div>
    </x-form>

    <div class="text-center text-sm mt-4">
        {{ __('Or, return to') }}
        <a href="{{ route('login') }}" wire:navigate.hover class="link">
            {{ __('log in') }}
        </a>
    </div>
</x-card>
