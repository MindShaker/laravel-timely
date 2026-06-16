@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium bg-nav-active-bg text-nav-active-fg leading-5 transition duration-150 ease-in-out'
    : 'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-nav-fg hover:bg-nav-hover-bg leading-5 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
