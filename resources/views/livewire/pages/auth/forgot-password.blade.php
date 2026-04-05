<x-card
    :title="__('Forgot password')"
    :subtitle="__('Enter your email to receive a password reset link')"
    separator
    shadow
    class="mx-auto w-full max-w-lg"
>
    <x-auth-session-status :status="session('status')" class="text-center" />

    <x-form wire:submit="sendPasswordResetLink" novalidate>
        <x-input
            icon="o-at-symbol"
            :label="__('Email address')"
            wire:model="email"
            autofocus
            autocomplete="username"
            required
        />

        <hr class="mt-3" />

        <div class="flex w-full justify-center">
            <x-button
                icon="o-envelope"
                :label="__('Email password reset link')"
                spinner="sendPasswordResetLink"
                type="submit"
                class="btn-primary"
            />
        </div>
    </x-form>

    <div class="mt-4 text-center text-sm">
        {{ __('Or, return to') }}
        <a href="{{ route('login') }}" wire:navigate.hover class="link"> {{ __('log in') }} </a>
    </div>
</x-card>
