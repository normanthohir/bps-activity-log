@props([
    'type' => 'submit',
    'onclick' => null, // Alpine expression, misal: "submit()" atau "submit('ajukan')"
    'loading' => 'loading', // nama variable Alpine yang menandakan proses sedang berjalan
    'loadingText' => 'Memproses...',
    'variant' => 'solid', // 'solid' (navy) atau 'outline' (putih border abu-abu)
])

@php
    $styles = match ($variant) {
        'outline' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-300/40',
        default => 'bg-[#1F3864] hover:bg-[#16294a] text-white focus:ring-[#1F3864]/30',
    };
@endphp

<button type="{{ $type }}" @if ($onclick) @click="{{ $onclick }}" @endif
    :disabled="{{ $loading }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors disabled:opacity-70 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 $styles"]) }}>
    <x-button-spinner x-show="{{ $loading }}" x-cloak />
    <span x-show="!({{ $loading }})" x-cloak>{{ $slot }}</span>
    <span x-show="{{ $loading }}" x-cloak>{{ $loadingText }}</span>
</button>
