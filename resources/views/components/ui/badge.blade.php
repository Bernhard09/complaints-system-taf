@props(['type' => 'default'])

@php
$base = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium";

$colors = [
    'default' => "bg-gray-100 text-gray-800",
    'submitted' => "bg-gray-100 text-gray-800",
    'assigned' => "bg-blue-100 text-blue-800",
    'in_progress' => "bg-indigo-100 text-indigo-800",
    'waiting_user' => "bg-amber-100 text-amber-800",
    'resolved' => "bg-green-100 text-green-800",
    'escalation_l1' => "bg-orange-100 text-orange-800",
    'escalation_l2' => "bg-red-100 text-red-800",
    'escalation_l3' => "bg-red-200 text-red-900",
];

$style = $colors[$type] ?? $colors['default'];
@endphp

<span class="{{ $base }} {{ $style }}">
    {{ $slot }}
</span>
