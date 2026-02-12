@props(['class' => ''])

<div {{ $attributes->merge([
    'class' => "bg-white border border-gray-200 rounded-xl shadow-sm p-6 $class"
]) }}>
    {{ $slot }}
</div>
