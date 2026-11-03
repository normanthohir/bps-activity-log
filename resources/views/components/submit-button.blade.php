@props([
    'type' => 'submit',
    'onclick' => null, 
    'loading' => 'loading', 
    'variant' => 'solid', 
])

@php
    $styles = match ($variant) {
        'outline' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-300/40',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-600/30',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-600/30',
        default => 'bg-[#1F3864] hover:bg-[#16294a] text-white focus:ring-[#1F3864]/30',
    };
@endphp

<button type="{{ $type }}" @if ($onclick) @click="{{ $onclick }}" @endif
    :disabled="{{ $loading }}"
    {{ $attributes->merge(['class' => "relative inline-flex items-center justify-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors disabled:opacity-70 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 $styles"]) }}>
    
    <div x-show="{{ $loading }}" x-cloak class="absolute inset-0 flex items-center justify-center">
        <x-button-spinner />
    </div>

    <span :class="{ 'invisible': {{ $loading }} }">
        {{ $slot }}
    </span>
</button>
