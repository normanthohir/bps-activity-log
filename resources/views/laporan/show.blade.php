{{-- resources/views/laporan/show.blade.php --}}
@php
    $isStaf = auth()->user()->role === 'staf';
    $routeIndex = $isStaf ? 'laporan.index' : 'kabag.laporan.index';
    $routeEdit = $isStaf ? 'laporan.edit' : 'kabag.laporan.edit';

    // Laporan boleh diubah selama masih draft atau dikembalikan (sesuai LaporanHarianPolicy)
    $bisaDiubah = in_array($laporan->status, ['draft', 'dikembalikan']);

    // Gaya tampilan untuk masing-masing output
    $outputGaya = match ($laporan->output) {
        'selesai' => ['box' => 'bg-green-50 border-green-200', 'judul' => 'text-green-800', 'isi' => 'text-green-700',
                      'ikon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Selesai'],
        'gagal' => ['box' => 'bg-red-50 border-red-200', 'judul' => 'text-red-800', 'isi' => 'text-red-700',
                    'ikon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Gagal'],
        'tertunda' => ['box' => 'bg-amber-50 border-amber-200', 'judul' => 'text-amber-800', 'isi' => 'text-amber-700',
                       'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Tertunda'],
        // data lama (teks bebas sebelum output jadi dropdown)
        default => ['box' => 'bg-gray-50 border-gray-200', 'judul' => 'text-gray-800', 'isi' => 'text-gray-600',
                    'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => $laporan->output],
    };

    $lokasiGaya = match ($laporan->lokasi) {
        'kantor' => 'bg-blue-50 text-blue-700',
        'lapangan' => 'bg-green-50 text-green-700',
        'dinas_luar' => 'bg-purple-50 text-purple-700',
        default => 'bg-gray-100 text-gray-600',
    };

    // Lama kegiatan (misal "3 jam 30 menit")
    $durasi = null;
    if ($laporan->jam_mulai && $laporan->jam_selesai) {
        $menit = (int) abs(\Carbon\Carbon::parse($laporan->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($laporan->jam_selesai)));
        $durasi = trim(($menit >= 60 ? intdiv($menit, 60) . ' jam ' : '') . ($menit % 60 ? $menit % 60 . ' menit' : ''));
    }

    $logTerakhir = $laporan->logApproval->last();
@endphp

<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Tombol kembali --}}
        <a href="{{ route($routeIndex) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar laporan
        </a>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detail Laporan</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $laporan->tanggal->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$laporan->status" />
                @if ($bisaDiubah)
                    <a href="{{ route($routeEdit, $laporan) }}"
                        class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                        {{ $laporan->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Catatan penolakan dari atasan (paling penting, ditaruh di atas) --}}
        @if ($laporan->status === 'dikembalikan' && $logTerakhir)
            <div class="flex gap-3 bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Laporan dikembalikan, mohon direvisi</p>
                    <p class="text-sm text-red-700 mt-1">{{ $logTerakhir->catatan ?? 'Tidak ada catatan.' }}</p>
                    <p class="text-xs text-red-500 mt-2">
                        {{ $logTerakhir->approver->name ?? '-' }} &middot; {{ $logTerakhir->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Ringkasan: tanggal, waktu, lokasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Tanggal</p>
                <p class="text-sm font-semibold text-gray-900">{{ $laporan->tanggal->format('d M Y') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Waktu kegiatan</p>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $laporan->jam_mulai ? substr($laporan->jam_mulai, 0, 5) : '--:--' }}
                    &ndash;
                    {{ $laporan->jam_selesai ? substr($laporan->jam_selesai, 0, 5) : '--:--' }}
                </p>
                @if ($durasi)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $durasi }}</p>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Lokasi</p>
                <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $lokasiGaya }}">
                    {{ ucwords(str_replace('_', ' ', $laporan->lokasi)) }}
                </span>
            </div>
        </div>

        {{-- Isi laporan --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 mb-6">

            @if ($laporan->tugas)
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Terkait tugas</p>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-sm font-medium text-blue-900">{{ $laporan->tugas->judul }}</p>
                        @if ($laporan->tugas->pemberiTugas)
                            <p class="text-xs text-blue-600 mt-1">
                                Diberikan oleh {{ $laporan->tugas->pemberiTugas->name }}
                                ({{ $laporan->tugas->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }})
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Uraian kegiatan</p>
                <p class="text-sm text-gray-900 leading-relaxed">{!! nl2br(e($laporan->uraian)) !!}</p>
            </div>

            <div class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Output / hasil</p>
                <div class="border rounded-lg p-4 {{ $outputGaya['box'] }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $outputGaya['judul'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $outputGaya['ikon'] }}" />
                        </svg>
                        <p class="text-sm font-semibold {{ $outputGaya['judul'] }}">{{ ucfirst($outputGaya['label']) }}</p>
                    </div>

                    @if ($laporan->alasan)
                        <div class="mt-3 pt-3 border-t border-black/5">
                            <p class="text-xs font-medium {{ $outputGaya['judul'] }} mb-1">
                                Alasan {{ $laporan->output }}
                            </p>
                            <p class="text-sm {{ $outputGaya['isi'] }} leading-relaxed">{!! nl2br(e($laporan->alasan)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Lampiran bukti</p>
                @if ($laporan->file_lampiran)
                    <a href="{{ $laporan->file_lampiran }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm font-medium text-[#1F3864] bg-[#1F3864]/5 hover:bg-[#1F3864]/10 px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Buka lampiran
                    </a>
                @else
                    <p class="text-sm text-gray-400">Tidak ada lampiran.</p>
                @endif
            </div>
        </div>

        {{-- Riwayat laporan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">Riwayat laporan</p>

            <ol class="relative border-s border-gray-200 ms-2 space-y-5">

                {{-- Laporan dibuat --}}
                <li class="ms-5">
                    <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                    <p class="text-sm font-medium text-gray-900">Laporan dibuat</p>
                    <p class="text-xs text-gray-400">{{ $laporan->created_at->format('d M Y, H:i') }}</p>
                </li>

                {{-- Setiap keputusan atasan --}}
                @foreach ($laporan->logApproval as $log)
                    @php $disetujui = $log->aksi === 'disetujui'; @endphp
                    <li class="ms-5">
                        <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full {{ $disetujui ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $disetujui ? 'Disetujui' : 'Dikembalikan' }} oleh {{ $log->approver->name ?? '-' }}
                            </p>
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full {{ $disetujui ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $log->approver && $log->approver->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</p>
                        @if ($log->catatan)
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 mt-2">{{ $log->catatan }}</p>
                        @endif
                    </li>
                @endforeach

                {{-- Masih menunggu --}}
                @if ($laporan->status === 'menunggu')
                    <li class="ms-5">
                        <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <p class="text-sm font-medium text-amber-700">Menunggu persetujuan atasan</p>
                    </li>
                @endif
            </ol>
        </div>

    </div>
</x-app-layout>