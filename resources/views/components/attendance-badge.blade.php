@props(['status'])

@php
    $tone = [
        'present' => 'green',
        'late' => 'amber',
        'absent' => 'red',
        'excused' => 'blue',
    ][$status] ?? 'slate';
@endphp

<x-badge :tone="$tone" {{ $attributes }}>{{ \App\Models\SessionAttendance::STATUS_LABELS[$status] ?? $status }}</x-badge>
