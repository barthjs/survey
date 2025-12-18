@props([
    'status',
])
@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-center text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif
