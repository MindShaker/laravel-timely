@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-input-border bg-input text-content placeholder:text-input-placeholder shadow-sm focus:border-input-ring focus:ring-input-ring focus:outline-none']) }}>
