@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-navy-900">
                <x-icon name="chevron-left" class="size-4" /> Volver
            </a>
        @endif
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
