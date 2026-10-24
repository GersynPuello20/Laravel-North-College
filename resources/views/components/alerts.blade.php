@php
    $messages = array_filter([
        'success' => session('success'),
        'error' => session('error'),
        'info' => session('info') ?? session('status'),
    ]);
    $styles = [
        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'check-circle'],
        'error' => ['border-accent-200 bg-accent-50 text-accent-700', 'exclamation-triangle'],
        'info' => ['border-sky-200 bg-sky-50 text-sky-800', 'information-circle'],
    ];
@endphp

@if ($messages || $errors->any())
    <div class="mb-6 space-y-3" role="status">
        @foreach ($messages as $type => $message)
            <div x-data="{ show: true }" x-show="show" class="flex items-start gap-3 rounded-lg border px-4 py-3 text-sm {{ $styles[$type][0] }}">
                <x-icon :name="$styles[$type][1]" class="mt-0.5 size-5 shrink-0" />
                <p class="flex-1">{{ $message }}</p>
                <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Cerrar aviso">
                    <x-icon name="x-mark" class="size-4" />
                </button>
            </div>
        @endforeach

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-lg border px-4 py-3 text-sm {{ $styles['error'][0] }}">
                <x-icon name="exclamation-triangle" class="mt-0.5 size-5 shrink-0" />
                <div>
                    <p class="font-medium">Revisa los datos del formulario:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif
