<x-card title="{{ __('Reset password') }}" subtitle="{{ __('Please enter your new password below') }}"
        shadow separator>

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="resetPassword">
        <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol" autofocus
                 autocomplete="username"
        />
        <x-password required label="{{ __('Password') }}" wire:model="password" required
                    autocomplete="new-password"/>
        <x-password required label="{{ __('Confirm password') }}" wire:model="password_confirmation" required
                    autocomplete="new-password"/>

        <x-button label="{{ __('Reset password') }}" type="submit" class="btn-primary" spinner="resetPassword"/>
    </x-form>
</x-card>
