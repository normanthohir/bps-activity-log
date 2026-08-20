@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#1F3864] focus:ring-[#1F3864]/20 rounded-lg shadow-sm']) }}>
