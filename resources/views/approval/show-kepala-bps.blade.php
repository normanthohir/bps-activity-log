{{-- resources/views/approval/show-kepala-bps.blade.php --}}
@php
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
                    'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => $laporan->output ?? '-'],
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
@endphp

<x-app-layout>
    <style>
        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fade-slide-up 0.5s ease-out both; }
        .delay-1 { animation-delay: .05s; }
        .delay-2 { animation-delay: .1s; }
        .delay-3 { animation-delay: .15s; }
    </style>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Tombol kembali --}}
        <a href="{{ route('kepala-bps.approval.index') }}"
            class="animate-in inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar persetujuan
        </a>

        {{-- Kartu pegawai --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-full bg-[#DCE6F1] text-[#1F3864] text-base font-semibold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 text-lg leading-tight">{{ $laporan->user->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $laporan->user->bagian->nama_bagian ?? '-' }}
                            &middot; {{ $laporan->user->role === 'kepala_bagian' ? 'Kepala Tim' : 'Staf' }}
                        </p>
                    </div>
                </div>
                <x-status-badge :status="$laporan->status" />
            </div>
        </div>

        {{-- Ringkasan: tanggal, waktu, lokasi --}}
        <div class="animate-in delay-1 grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
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
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 mb-4">

            {{-- Asal laporan: terkait tugas atau mandiri --}}
            <div class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Asal laporan</p>
                @if ($laporan->tugas)
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-sm font-medium text-blue-900">{{ $laporan->tugas->judul }}</p>
                        @if ($laporan->tugas->pemberiTugas)
                            <p class="text-xs text-blue-600 mt-1">
                                Tugas dari {{ $laporan->tugas->pemberiTugas->name }}
                                ({{ $laporan->tugas->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }})
                            </p>
                        @endif
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600">
                        Laporan mandiri, tidak terkait tugas dari atasan.
                    </div>
                @endif
            </div>

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
                            <p class="text-xs font-medium {{ $outputGaya['judul'] }} mb-1">Alasan {{ $laporan->output }}</p>
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

        {{-- Keputusan: hanya muncul kalau masih menunggu --}}
        @if ($laporan->status === 'menunggu')
            <div class="animate-in delay-2 bg-white rounded-xl border-2 border-amber-200 p-5 mb-4"
                x-data="{ tolak: {{ $errors->has('catatan') ? 'true' : 'false' }} }">

                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <p class="text-sm font-semibold text-gray-900">Laporan ini menunggu keputusan Anda</p>
                </div>

                {{-- Tombol utama --}}
                <div x-show="!tolak" x-transition class="flex gap-2">
                    <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                        x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="aksi" value="disetujui">
                        <x-loading-overlay message="Memproses persetujuan..." />
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-70 disabled:cursor-not-allowed text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                            <x-button-spinner x-show="loading" x-cloak />
                            <span x-text="loading ? 'Memproses...' : 'Setujui'"></span>
                        </button>
                    </form>

                    <button type="button" @click="tolak = true"
                        class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                        Tolak
                    </button>
                </div>

                {{-- Form penolakan --}}
                <form x-show="tolak" x-cloak x-transition method="POST"
                    action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                    x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <input type="hidden" name="aksi" value="ditolak">

                    <label class="text-sm font-medium text-gray-700 block mb-1.5">
                        Alasan penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan" rows="3" required
                        class="w-full rounded-lg text-sm focus:ring-red-500/20 @error('catatan') border-red-500 @else border-gray-300 @enderror"
                        placeholder="Jelaskan apa yang perlu diperbaiki...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    <x-loading-overlay message="Mengirim penolakan..." />

                    <div class="flex gap-2 mt-3">
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-70 disabled:cursor-not-allowed text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                            <x-button-spinner x-show="loading" x-cloak />
                            <span x-text="loading ? 'Mengirim...' : 'Kirim penolakan'"></span>
                        </button>
                        <button type="button" @click="tolak = false"
                            class="bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-300 text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Riwayat persetujuan --}}
        <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">Riwayat laporan</p>

            <ol class="relative border-s border-gray-200 ms-2 space-y-5">

                <li class="ms-5">
                    <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                    <p class="text-sm font-medium text-gray-900">Laporan dibuat oleh {{ $laporan->user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $laporan->created_at->format('d M Y, H:i') }}</p>
                </li>

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

                @if ($laporan->status === 'menunggu')
                    <li class="ms-5">
                        <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <p class="text-sm font-medium text-amber-700">Menunggu keputusan</p>
                    </li>
                @endif
            </ol>
        </div>

    </div>
</x-app-layout>