<button style="cursor: pointer" {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2 rounded-none font-medium text-sm bg-yellow-500 text-white border border-solid border-transparent hover:bg-transparent hover:text-yellow-500 hover:border-yellow-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 transition-all duration-150']) }}>
    {{ $slot }}
</button>