@props([
    'title',
    'value',
    'color' => 'gray',
    'link' => null,
])

@php
    $colorMap = [
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'green' => 'bg-green-100 text-green-700',
        'gray' => 'bg-gray-100 text-gray-700',
    ];

    $iconColor = $colorMap[$color] ?? $colorMap['gray'];
@endphp

<div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $title }}</p>
            <p class="text-3xl font-semibold mt-2">{{ $value }}</p>
        </div>

        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $iconColor }}">
            <div class="w-5 h-5 bg-current rounded"></div>
        </div>
    </div>

    @if($link)
        <a href="{{ $link }}" class="text-sm text-indigo-600 mt-4 inline-block">
            View details →
        </a>
    @endif
</div>
