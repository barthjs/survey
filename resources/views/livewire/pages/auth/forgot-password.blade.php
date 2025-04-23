<x-card title="{{ __('Forgot password') }}" subtitle="{{ __('Enter your email to receive a password reset link') }}"
        shadow separator class="w-full max-w-lg mx-auto">

    <x-auth-session-status class="text-center" :status="session('status')"/>

    <x-form novalidate wire:submit="sendPasswordResetLink">
        <x-input required label="{{ __('Email address') }}" wire:model="email" icon="o-at-symbol" autofocus
                 autocomplete="username"/>

        <hr class="mt-3"/>

        <x-button label="{{ __('Email password reset link') }}" type="submit" icon="o-envelope"
                  class="btn-primary w-full" spinner="sendPasswordResetLink"/>

        <div class="text-center text-sm mt-4">
            {{ __('Or, return to') }}
            <a class="link" href="{{ route('login') }}">{{ __('log in') }}</a>
        </div>
    </x-form>
</x-card>
