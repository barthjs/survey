@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="auth min-h-screen flex items-center justify-center font-sans antialiased bg-base-200">

    {{ $slot }}

    </body>
</x-layouts.html>
