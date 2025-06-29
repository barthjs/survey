<x-layouts.web>
    <div class="text-center p-8 space-y-4">
        <h1 class="text-3xl">{{ config('app.name') }}</h1>
        <a href="{{ route('login') }}" wire:navigate.hover class="font-medium underline px-3">
            {{ __('Log in')}}
        </a>
        @if(config('app.allow_registration'))
            <a href="{{ route('register') }}" wire:navigate.hover class="font-medium underline px-3">
                {{ __('Register')}}
            </a>
        @endif
        <div class="flex justify-center pt-4 gap-x-6">
            <x-language-selector class="btn-circle btn-ghost"/>
            <x-theme-toggle darkTheme="night" lightTheme="fantasy" class="btn-circle btn-ghost"/>
        </div>
    </div>

    @livewireScripts
</x-layouts.web>
