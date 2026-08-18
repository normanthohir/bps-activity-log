{{-- resources/views/components/status-badge.blade.php --}}
@props(['status'])

@php
    $warna = match($status) {
        'disetujui' => 'bg-green-100 text-green-700',
        'menunggu' => 'bg-amber-100 text-amber-700',
        'dikembalikan' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-600', // draft
    };

    $label = match($status) {
        'disetujui' => 'Disetujui',
        'menunggu' => 'Menunggu',
        'dikembalikan' => 'Dikembalikan',
        default => 'Draft',
    };
@endphp

<span class="{{ $warna }} text-xs px-2 py-0.5 rounded-full">{{ $label }}</span>
