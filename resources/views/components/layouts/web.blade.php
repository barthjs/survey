@props (['title' => null])
<x-layouts.html :title="$title">
    <body
        class="web bg-base-200 flex min-h-screen items-center justify-center font-sans antialiased"
    >
        {{ $slot }}
    </body>
</x-layouts.html>
