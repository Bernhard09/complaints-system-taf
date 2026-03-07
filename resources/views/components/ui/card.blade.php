@props(['class' => ''])

<div {{ $attributes->merge([
    'class' => " hover:-translate-y-1 transition-all duration-200 bg-white border border-gray-200 rounded-xl shadow-sm p-6 $class"
]) }}>
    {{ $slot }}
</div>
