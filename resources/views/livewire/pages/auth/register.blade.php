<x-card title="{{ __('Create an account') }}" subtitle="{{ __('Enter your details below to create your account') }}"
        shadow separator class="w-full max-w-lg mx-auto">

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="register">
        <x-input required :label="__('Full name')" wire:model="name" icon="o-user"/>
        <x-input required :label="__('Email address')" wire:model="email" icon="o-at-symbol"/>
        <x-password required :label="__('Password')" wire:model="password"/>
        <x-password required :label=" __('Confirm Password')" wire:model="password_confirmation"/>

        <hr class="mt-3"/>

        <x-button label="{{ __('Create Account') }}" type="submit" icon="o-paper-airplane" class="btn-primary"
                  spinner="register"/>

        <div class="text-center text-sm mt-4">
            <div>{{ __('Already have an account?') }}
                <a href="{{ route('login') }}" wire:navigate.hover class="link">{{ __('Log in') }}</a>
            </div>
        </div>
    </x-form>
</x-card>
