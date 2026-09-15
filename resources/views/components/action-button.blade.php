@props([
    'href',
    'variant' => 'solid', // 'solid' = navy di atas background putih, 'light' = putih di atas background gelap/gradient
])

@php
    $styles = match ($variant) {
        'light' => 'bg-white text-[#1F3864] hover:bg-white/90 shadow-sm',
        default => 'bg-[#1F3864] text-white hover:bg-[#16294a]',
    };
@endphp

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => "relative inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2.5 rounded-lg transition-all hover:scale-[1.03] active:scale-[0.98] $styles"]) }}>
    {{-- Icon: pakai icon default (+) kalau tidak dikirim slot 'icon' --}}
    @isset($icon)
        {{ $icon }}
    @else
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    @endisset

    {{ $slot }}
</a>