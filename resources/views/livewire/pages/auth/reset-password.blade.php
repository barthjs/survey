<x-card title="{{ __('Reset password') }}" subtitle="{{ __('Please enter your new password below') }}"
        shadow separator class="w-full max-w-lg mx-auto">

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form wire:submit="resetPassword" novalidate>
        <x-input required :label="__('Email address')" wire:model="email" icon="o-at-symbol" autofocus
                 autocomplete="username"/>
        <x-password required :label=" __('Password')" wire:model="password" autocomplete="new-password"/>
        <x-password required :label="__('Confirm password')" wire:model="password_confirmation"
                    autocomplete="new-password"/>

        <hr class="mt-3"/>

        <x-button :label="__('Reset password')" type="submit" class="btn-primary" spinner="resetPassword"/>
    </x-form>
</x-card>
