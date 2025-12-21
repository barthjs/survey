<x-card
    :title="__('Two Factor Authentication')"
    separator
    shadow
    class="w-full max-w-lg mx-auto"
    x-data="{ code: ''}"
>
    <div class="mb-4 text-sm">
        @if (!$recovery)
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        @else
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        @endif
    </div>

    <x-form wire:submit="login">
        @if (!$recovery)
            <x-input
                x-model="code"
                @input="$wire.set('code', code); if(code.length === 6) $wire.login()"
                autofocus
                autocomplete="one-time-code"
            />
        @else
            <x-input
                :label="__('Recovery Code')"
                x-model="recovery_code"
                autofocus
                autocomplete="one-time-code"
            />
        @endif

        <div class="flex items-center justify-end mt-4">
            <x-button
                :label="$recovery ? __('Use an authentication code') : __('Use a recovery code')"
                wire:click="toggleRecovery"
                class="btn-ghost"
            />

            <x-button
                :label="__('Log in')"
                type="submit"
                class="btn-primary"
                spinner="login"
            />
        </div>
    </x-form>
</x-card>
