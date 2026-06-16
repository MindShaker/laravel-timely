@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full px-4 py-2.5 text-left text-sm font-medium bg-nav-active-bg text-nav-active-fg transition duration-150 ease-in-out'
    : 'block w-full px-4 py-2.5 text-left text-sm font-medium text-nav-fg hover:bg-nav-hover-bg transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
