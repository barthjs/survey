<x-card title="{{ __('Log in to your account') }}" subtitle="{{ __('Enter your email and password below to log in') }}"
        shadow separator>

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="login">
        <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol" autofocus
                 autocomplete="username"
        />
        <x-password required label="{{ __('Password') }}" wire:model="password" autocomplete="current-password"/>
        <x-checkbox label="{{ __('Remember Me') }}" wire:model="form.remember"/>

        <div>
            <a class="link" href="{{ route('password.request') }}"
               wire:navigate.hover>{{ __('Forgot your password?') }}</a>
        </div>
        <x-button label="{{ __('Log in') }}" type="submit" icon="o-arrow-right-end-on-rectangle" class="btn-primary"
                  spinner="login"/>

        <div>
            {{ __('Don\'t have an account?') }}
            <a class="link" href="{{ route('register') }}" wire:navigate.hover>{{ __('Sign up') }}</a>
        </div>
        @error('')  @enderror
    </x-form>
</x-card>
