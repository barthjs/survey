<x-layouts.web>
    <div class="text-center p-8">
        <h1 class="text-3xl">{{ config('app.name') }}</h1>
        <p>{{ config('app.version') }}</p>
        <a href="{{ route('login') }}" wire:navigate.hover class="font-medium underline px-3">
            {{ __('Log in ')}}
        </a>
        @if(config('app.allow_registration'))
            <a href="{{ route('register') }}" wire:navigate.hover class="font-medium underline px-3">
                {{ __('Register')}}
            </a>
        @endif
    </div>
</x-layouts.web>
