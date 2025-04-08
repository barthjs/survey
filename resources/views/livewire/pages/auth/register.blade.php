<x-card title="{{ __('Create an account') }}" subtitle="{{ __('Enter your details below to create your account') }}"
        shadow separator>

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="register">
        <x-input required label="{{ __('Full name') }}" wire:model="name" icon="o-user"/>
        <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol"/>
        <x-password required label="{{ __('Password') }}" wire:model="password"/>
        <x-password required label="{{ __('Confirm Password') }}" wire:model="password_confirmation"/>

        <x-button label="{{ __('Create Account') }}" type="submit" icon="o-paper-airplane" class="btn-primary"
                  spinner="register"/>

        <div>
            {{ __('Already have an account?') }}
            <a class="link" href="{{ route('login') }}" wire:navigate.hover>{{ __('Log in') }}</a>
        </div>
    </x-form>
</x-card>
