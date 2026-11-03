{{-- Parameter: $aktif ('laporan'|'pekerjaan'), $judul, $periode, $keterangan (opsional), $bagianDipilih (opsional),
     $routeExcel, $routePdf, $parameterUnduh --}}
@php
    $parameterTetap = request()->only(['bulan', 'tahun', 'bagian']);
    $tabAktif = 'bg-white text-gray-900 shadow-sm';
    $tabBiasa = 'text-gray-500 hover:text-gray-700';
@endphp

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
        <div class="inline-flex bg-gray-100 rounded-lg p-1 mb-3">
            <a href="{{ route('kepala-bps.rekap', $parameterTetap) }}"
                class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors {{ $aktif === 'laporan' ? $tabAktif : $tabBiasa }}">
                Laporan harian
            </a>
            <a href="{{ route('kepala-bps.rekap.pekerjaan', $parameterTetap) }}"
                class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors {{ $aktif === 'pekerjaan' ? $tabAktif : $tabBiasa }}">
                Pekerjaan
            </a>
        </div>

        <h1 class="text-2xl font-semibold text-gray-900">{{ $judul }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            Periode <span class="font-medium text-gray-700">{{ $periode }}</span>
            @if (!empty($keterangan))
                &middot; {{ $keterangan }}
            @endif
            @if (!empty($bagianDipilih))
                &middot; {{ $bagianDipilih->nama_bagian }}
            @endif
        </p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route($routeExcel, $parameterUnduh) }}"
            class="inline-flex items-center gap-2 bg-white hover:bg-green-50 text-green-700 border border-green-300 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Unduh Excel
        </a>
        <a href="{{ route($routePdf, $parameterUnduh) }}"
            class="inline-flex items-center gap-2 bg-white hover:bg-red-50 text-red-700 border border-red-300 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Unduh PDF
        </a>
    </div>
</div>
