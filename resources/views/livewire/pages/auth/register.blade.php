<x-card
    :title="__('Create an account')"
    :subtitle="__('Enter your details below to create your account')"
    separator
    shadow
    class="w-full max-w-lg mx-auto"
>
    <x-auth-session-status :status="session('status')" class="text-center"/>

    <x-form wire:submit="register" novalidate>
        <x-input icon="o-user" :label="__('Full name')" wire:model="name" autofocus autocomplete="name" required/>
        <x-input icon="o-at-symbol" :label="__('Email address')" wire:model="email" autocomplete="email" required/>

        <x-password :label="__('Password')" wire:model="password" required/>
        <x-password :label=" __('Confirm Password')" wire:model="password_confirmation" required/>

        <hr class="mt-3"/>

        <div class="w-full flex justify-center">
            <x-button
                icon="o-paper-airplane"
                :label="__('Create Account')"
                spinner="register"
                type="submit"
                class="btn-primary"
            />
        </div>
    </x-form>

    <div class="text-center text-sm mt-4">
        <div>{{ __('Already have an account?') }}
            <a href="{{ route('login') }}" wire:navigate.hover class="link">
                {{ __('Log in') }}
            </a>
        </div>
    </div>
</x-card>
