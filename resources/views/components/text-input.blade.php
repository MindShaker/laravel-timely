@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-gray-700 border border-gray-600 text-gray-100 text-sm focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 placeholder-gray-400']) }}>
