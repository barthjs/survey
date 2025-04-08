<x-card title="{{ __('Forgot password') }}" subtitle="{{ __('Enter your email to receive a password reset link') }}"
        shadow separator>

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="sendPasswordResetLink">
        <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol" autofocus
                 autocomplete="username"
        />

        <x-button label="{{ __('Email password reset link') }}" type="submit" icon="o-envelope" class="btn-primary"
                  spinner="sendPasswordResetLink"/>

        <div>
            {{ __('Or, return to') }}
            <a class="link" href="{{ route('login') }}">{{ __('log in') }}</a>
        </div>
    </x-form>
</x-card>
