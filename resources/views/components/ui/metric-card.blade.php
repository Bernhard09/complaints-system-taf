@props([
    'label',
    'value',
    'icon' => null,
    'link' => null
])

<x-ui.card class="flex flex-col justify-between h-full">

    <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500">
            {{ $label }}
        </span>

        @if($icon)
            <span class="text-gray-400">
                {!! $icon !!}
            </span>
        @endif
    </div>

    <div class="mt-4">
        <span class="text-3xl font-semibold text-gray-900">
            {{ $value }}
        </span>
    </div>

    @if($link)
        <div class="mt-4">
            <a href="{{ $link }}" class="text-sm text-indigo-600 hover:underline">
                View details →
            </a>
        </div>
    @endif

</x-ui.card>
