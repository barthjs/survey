@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="web min-h-screen font-sans antialiased bg-base-200">

    <x-nav sticky>
        <x-slot:brand>
            <div>
                <a href="{{ route('home') }}" wire:navigate.hover class="text-2xl">
                    {{ config('app.name') }}
                </a>
            </div>
        </x-slot:brand>

        <x-slot:actions>
            <x-language-selector class="btn-circle btn-ghost"/>
            <x-theme-toggle darkTheme="night" lightTheme="fantasy" class="btn-circle btn-ghost"/>
        </x-slot:actions>
    </x-nav>

    <x-main>
        <x-slot:content class="flex items-center justify-center">
            {{ $slot }}
        </x-slot:content>

        <x-slot:footer>
            <x-footer/>
        </x-slot:footer>
    </x-main>

    @vite('resources/js/app.js')

    </body>
</x-layouts.html>
