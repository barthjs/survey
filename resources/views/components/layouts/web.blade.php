@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="web min-h-screen">

    {{ $slot }}

    </body>
</x-layouts.html>
