<x-layouts.web>
    <div class="space-y-4 p-8 text-center">
        <h1 class="text-3xl">{{ config('app.name') }}</h1>
        <a href="{{ route('login') }}" wire:navigate.hover class="px-3 font-medium underline">
            {{ __('Log in')}}
        </a>
        @if (config('app.allow_registration'))
            <a
                href="{{ route('register') }}"
                wire:navigate.hover
                class="px-3 font-medium underline"
            >
                {{ __('Register')}}
            </a>
        @endif
        <div class="flex justify-center gap-x-6 pt-4">
            <x-language-selector class="btn-circle btn-ghost" />
            <x-theme-toggle darkTheme="night" lightTheme="fantasy" class="btn-circle btn-ghost" />
        </div>
    </div>

    @livewireScripts
</x-layouts.web>
