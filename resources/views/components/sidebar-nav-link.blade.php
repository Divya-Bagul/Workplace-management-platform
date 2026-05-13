@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-2 rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700'
    : 'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
