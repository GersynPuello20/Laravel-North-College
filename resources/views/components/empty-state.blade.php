@props(['icon' => 'information-circle', 'title'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</p>
    @if (trim($slot) !== '')
        <p class="mt-1 max-w-md text-sm text-slate-500">{{ $slot }}</p>
    @endif
    @isset($actions)
        <div class="mt-5 flex flex-wrap justify-center gap-2">{{ $actions }}</div>
    @endisset
</div>
