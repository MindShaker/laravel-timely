<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-3 py-2 text-sm leading-5 text-danger-btn-fg transition duration-150 ease-in-out border border-danger-btn-border bg-danger-btn-bg hover:bg-danger-btn-hover rounded disabled:opacity-25']) }}>
    {{ $slot }}
</button>
