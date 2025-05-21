<x-card
    :title="__('Log in to your account')"
    separator
    shadow
    class="w-full max-w-lg mx-auto"
>
    <x-slot:subtitle>
        @if($wantsToVerifyEmail)
            {{ __('Please log in again to activate your account. Use the credentials you provided during registration.') }}
        @else
            {{ __('Enter your email and password below to log in') }}
        @endif
    </x-slot:subtitle>

    <x-auth-session-status :status="session('status')" class="text-center"/>

    <x-form wire:submit="login" novalidate>
        <x-input
            icon="o-at-symbol"
            :label="__('Email address')"
            wire:model="email"
            autofocus
            autocomplete="username"
            required
        />

        <x-password :label="__('Password')" wire:model="password" autocomplete="current-password" required/>
        <x-checkbox :label="__('Remember Me')" wire:model="remember"/>

        <hr class="mt-3"/>

        <div class="w-full flex justify-center">
            <x-button
                icon="o-arrow-right-end-on-rectangle"
                :label="__('Log in')"
                spinner="login"
                type="submit"
                class="btn-primary"
            />
        </div>
    </x-form>

    <div class="text-center text-sm mt-4 flex flex-col gap-y-3">
        @if(config('app.allow_registration'))
            <div>
                {{ __('Don\'t have an account?') }}
                <a href="{{ route('register') }}" wire:navigate.hover class="link">
                    {{ __('Sign up') }}
                </a>
            </div>
        @endif
        @if(config('app.enable_password_reset'))
            <div>
                <a href="{{ route('password.request') }}" wire:navigate.hover class="link">
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        @endif
    </div>
</x-card>
