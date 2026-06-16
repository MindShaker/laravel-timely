<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 rounded-md border border-danger-btn-border bg-danger-btn-bg text-xs font-semibold uppercase tracking-widest text-danger-btn-fg transition duration-150 ease-in-out hover:bg-danger-btn-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-surface disabled:opacity-25']) }}>
    {{ $slot }}
</button>
