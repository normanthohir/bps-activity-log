{{-- resources/views/components/status-badge.blade.php --}}
@props(['status'])

@php
    $style = match($status) {
        'disetujui' => 'bg-green-50 text-green-700 ring-green-600/20',
        'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'dikembalikan' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    };

    $dot = match($status) {
        'disetujui' => 'bg-green-500',
        'menunggu' => 'bg-amber-500',
        'dikembalikan' => 'bg-red-500',
        default => 'bg-gray-400',
    };

    $label = match($status) {
        'disetujui' => 'Disetujui',
        'menunggu' => 'Menunggu',
        'dikembalikan' => 'Dikembalikan',
        default => 'Draft',
    };
@endphp

<span class="inline-flex items-center gap-1.5 {{ $style }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
