@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="auth min-h-screen font-sans antialiased bg-base-200">

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
        <x-slot:content>
            <div class="flex flex-col justify-center min-h-[calc(100vh-3.5rem-2.5rem)]">
                {{ $slot }}
            </div>
        </x-slot:content>

        <x-slot:footer>
            <x-footer/>
        </x-slot:footer>
    </x-main>

    </body>
</x-layouts.html>
