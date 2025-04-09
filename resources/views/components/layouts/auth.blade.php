<x-layouts.html :title="$title">
    <body class="auth min-h-screen font-sans antialiased bg-primary-700 dark:bg-primary-800">
    <x-main full-width>
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    </body>
</x-layouts.html>
