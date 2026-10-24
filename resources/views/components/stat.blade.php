@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'navy', 'href' => null])

@php
    $iconTones = [
        'navy' => 'bg-navy-50 text-navy-800',
        'green' => 'bg-emerald-50 text-emerald-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'red' => 'bg-accent-50 text-accent-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card flex items-start gap-4 p-5'.($href ? ' transition hover:border-navy-300' : '')]) }}>
    @if ($icon)
        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $iconTones[$tone] ?? $iconTones['navy'] }}">
            <x-icon :name="$icon" />
        </span>
    @endif
    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>
