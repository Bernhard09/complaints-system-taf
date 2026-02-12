@props([
    'variant' => 'primary',
    'disabled' => false
])

@php
$base = "px-4 py-2 rounded-lg text-sm font-medium transition";

$variants = [
    'primary' => "bg-indigo-600 text-white hover:bg-indigo-700",
    'secondary' => "bg-gray-100 text-gray-700 hover:bg-gray-200",
    'danger' => "bg-red-600 text-white hover:bg-red-700",
];

$style = $variants[$variant] ?? $variants['primary'];

$disabledStyle = $disabled ? "opacity-50 cursor-not-allowed" : "";
@endphp

<button {{ $attributes->merge([
    'class' => "$base $style $disabledStyle"
]) }} @if($disabled) disabled @endif>
    {{ $slot }}
</button>
