<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-primary bg-primary-btn px-4 py-2 text-xs font-semibold uppercase tracking-widest text-on-primary transition duration-150 ease-in-out hover:bg-primary-btn-hover hover:border-primary-btn-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface active:bg-primary-btn disabled:opacity-25']) }}>
    {{ $slot }}
</button>
