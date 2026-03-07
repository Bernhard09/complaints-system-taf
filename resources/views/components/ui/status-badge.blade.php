@props(['status'])

@php
    $classes = match($status) {
        'IN_PROGRESS' => 'bg-indigo-100 text-indigo-700',
        'WAITING_USER' => 'bg-amber-100 text-amber-700',
        'RESOLVED' => 'bg-green-100 text-green-700',
        'ESCALATION_L1', 'ESCALATION_L2', 'ESCALATION_L3'
            => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span class="px-3 py-1 rounded-full text-xs font-medium {{ $classes }}">
    {{ __($status) }}
</span>
