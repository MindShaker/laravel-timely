<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-3 py-2 text-sm leading-5 text-content transition duration-150 ease-in-out hover:bg-chrome-hover border border-neutral-700 rounded disabled:opacity-25']) }}>
    {{ $slot }}
</button>
