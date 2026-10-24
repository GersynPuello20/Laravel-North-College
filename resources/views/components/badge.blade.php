@props(['tone' => 'slate'])

@php
    $tones = [
        'slate' => 'bg-slate-50 text-slate-700 ring-slate-200',
        'navy' => 'bg-navy-50 text-navy-800 ring-navy-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'red' => 'bg-accent-50 text-accent-700 ring-accent-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($tones[$tone] ?? $tones['slate'])]) }}>{{ $slot }}</span>
