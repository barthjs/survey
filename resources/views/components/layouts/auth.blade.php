<x-layouts.html :title="$title">
    <body
        class="auth min-h-screen flex items-center justify-center font-sans antialiased bg-primary-700 dark:bg-primary-800">
    <x-main>
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    </body>
</x-layouts.html>
