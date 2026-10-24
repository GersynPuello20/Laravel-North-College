@props(['session'])

@php
    $tone = ['scheduled' => 'slate', 'open' => 'green', 'closed' => 'navy'][$session->status] ?? 'slate';
@endphp

<x-badge :tone="$tone" {{ $attributes }}>
    @if ($session->isOpen())
        <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>
    @endif
    {{ $session->statusLabel() }}
</x-badge>
