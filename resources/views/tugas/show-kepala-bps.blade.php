{{-- resources/views/tugas/show-kepala-bps.blade.php --}}
@php
    $belumDikerjakan = $tugas->status === 'belum_dikerjakan';

    // Edit hanya boleh kalau tugas belum dikerjakan
    $bisaEdit = $belumDikerjakan;

    $terlambat = $tugas->tenggat && $tugas->tenggat->isPast() && ! $tugas->tenggat->isToday()
        && $tugas->status !== 'selesai';

    [$statusLabel, $statusGaya] = match ($tugas->status) {
        'selesai' => ['Selesai', 'bg-green-50 text-green-700 ring-green-600/20'],
        'dikerjakan' => ['Dikerjakan', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        default => ['Belum dikerjakan', 'bg-gray-50 text-gray-600 ring-gray-500/20'],
    };

    $peranPenerima = $tugas->penerimaTugas?->role === 'kepala_bagian' ? 'Kepala Tim' : 'Staf';
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
        <a href="{{ route('kepala-bps.tugas.index') }}"
            class="animate-in inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar tugas
        </a>

        {{-- Judul tugas + tombol edit --}}
        <div class="animate-in delay-1 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $tugas->judul }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset {{ $statusGaya }}">
                        {{ $statusLabel }}
                    </span>
                    @if ($terlambat)
                        <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                            Terlambat
                        </span>
                    @endif
                </div>
            </div>

            @if ($bisaEdit)
                <a href="{{ route('kepala-bps.tugas.edit', $tugas) }}"
                    class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors self-start">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Edit tugas
                </a>
            @else
                <p class="text-xs text-gray-400 sm:text-right self-start sm:max-w-[12rem]">
                    Tugas yang sudah dikerjakan tidak dapat diedit.
                </p>
            @endif
        </div>

        {{-- Kartu penerima tugas --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">Ditugaskan ke</p>
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-full bg-[#DCE6F1] text-[#1F3864] text-base font-semibold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($tugas->penerimaTugas?->name ?? '?', 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 text-lg leading-tight truncate">
                        {{ $tugas->penerimaTugas?->name ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-500 truncate">
                        {{ $tugas->bagian?->nama_bagian ?? '-' }} &middot; {{ $peranPenerima }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Ringkasan: tenggat, pemberi, tanggal dibuat --}}
        <div class="animate-in delay-2 grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Tenggat</p>
                @if ($tugas->tenggat)
                    <p class="text-sm font-semibold {{ $terlambat ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $tugas->tenggat->format('d M Y') }}
                    </p>
                    @if ($tugas->status !== 'selesai')
                        <p class="text-xs mt-0.5 {{ $terlambat ? 'text-red-500' : 'text-gray-400' }}">
                            {{ $tugas->tenggat->diffForHumans() }}
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-400">Tidak ada</p>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Diberikan oleh</p>
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $tugas->pemberiTugas?->name ?? '-' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $tugas->pemberiTugas?->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-1">Dibuat pada</p>
                <p class="text-sm font-semibold text-gray-900">{{ $tugas->created_at->format('d M Y') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $tugas->created_at->format('H:i') }}</p>
            </div>
        </div>

        {{-- Deskripsi --}}
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Deskripsi tugas</p>
            @if ($tugas->deskripsi)
                <p class="text-sm text-gray-900 leading-relaxed">{!! nl2br(e($tugas->deskripsi)) !!}</p>
            @else
                <p class="text-sm text-gray-400">Tidak ada deskripsi.</p>
            @endif
        </div>

        {{-- Laporan terkait --}}
        <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Laporan terkait tugas ini</p>
            </div>

            @if ($tugas->laporanHarian->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan</p>
                    <p class="text-sm text-gray-500 mt-1">Penerima tugas belum melaporkan progres tugas ini.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($tugas->laporanHarian as $laporan)
                        @php
                            $outputGaya = match ($laporan->output) {
                                'selesai' => 'bg-green-50 text-green-700',
                                'gagal' => 'bg-red-50 text-red-700',
                                'tertunda' => 'bg-amber-50 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                            $logTerakhir = $laporan->logApproval->last();
                            $formIni = old('_laporan') == $laporan->getKey();
                        @endphp

                        <div class="px-5 py-4">
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-1.5">
                                <p class="text-sm font-medium text-gray-900">{{ $laporan->tanggal->format('d M Y') }}</p>
                                <x-status-badge :status="$laporan->status" />
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed">{{ $laporan->uraian }}</p>

                            {{-- Output + alasan --}}
                            @if ($laporan->output)
                                <div class="mt-2 flex flex-wrap items-start gap-x-2 gap-y-1">
                                    <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-md {{ $outputGaya }}">
                                        {{ ucfirst($laporan->output) }}
                                    </span>
                                    @if ($laporan->alasan)
                                        <p class="text-xs text-gray-500 flex-1 min-w-[12rem]">Alasan: {{ $laporan->alasan }}</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Catatan penolakan terakhir --}}
                            @if ($laporan->status === 'dikembalikan' && $logTerakhir?->catatan)
                                <p class="mt-2 text-xs text-red-700 bg-red-50 rounded-lg px-3 py-2">
                                    Dikembalikan: {{ $logTerakhir->catatan }}
                                </p>
                            @endif

                            <a href="{{ route('kepala-bps.approval.show', $laporan) }}"
                                class="inline-flex items-center gap-1 mt-2 text-xs font-medium text-[#1F3864] hover:underline">
                                Lihat detail laporan
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>

                            {{-- Setujui / tolak: hanya untuk laporan menunggu yang boleh diproses --}}
                            @if ($laporan->status === 'menunggu')
                                @can('approve', $laporan)
                                    <div class="mt-4 rounded-xl border-2 border-amber-200 p-4"
                                        x-data="{ tolak: {{ $errors->has('catatan') && $formIni ? 'true' : 'false' }} }">

                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                            <p class="text-sm font-semibold text-gray-900">Menunggu keputusan Anda</p>
                                        </div>

                                        {{-- Tombol utama --}}
                                        <div x-show="!tolak" x-transition class="flex gap-2">
                                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                                                x-data="{ loading: false }" @submit="loading = true">
                                                @csrf
                                                <input type="hidden" name="aksi" value="disetujui">
                                                <x-loading-overlay message="Memproses persetujuan..." />
                                                <button type="submit" :disabled="loading"
                                                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-70 disabled:cursor-not-allowed text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                    <x-button-spinner x-show="loading" x-cloak />
                                                    <span x-text="loading ? 'Memproses...' : 'Setujui'"></span>
                                                </button>
                                            </form>

                                            <button type="button" @click="tolak = true"
                                                class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                Tolak
                                            </button>
                                        </div>

                                        {{-- Form penolakan --}}
                                        <form x-show="tolak" x-cloak x-transition method="POST"
                                            action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                                            x-data="{ loading: false }" @submit="loading = true">
                                            @csrf
                                            <input type="hidden" name="aksi" value="ditolak">
                                            {{-- Penanda laporan mana yang ditolak, supaya saat validasi gagal
                                                 hanya form laporan ini yang terbuka lagi --}}
                                            <input type="hidden" name="_laporan" value="{{ $laporan->getKey() }}">

                                            <label class="text-sm font-medium text-gray-700 block mb-1.5">
                                                Alasan penolakan <span class="text-red-500">*</span>
                                            </label>
                                            <textarea name="catatan" rows="3" required
                                                class="w-full rounded-lg text-sm focus:ring-red-500/20 @if ($errors->has('catatan') && $formIni) border-red-500 @else border-gray-300 @endif"
                                                placeholder="Jelaskan apa yang perlu diperbaiki...">{{ $formIni ? old('catatan') : '' }}</textarea>
                                            @if ($errors->has('catatan') && $formIni)
                                                <p class="text-sm text-red-600 mt-1">{{ $errors->first('catatan') }}</p>
                                            @endif

                                            <x-loading-overlay message="Mengirim penolakan..." />

                                            <div class="flex gap-2 mt-3">
                                                <button type="submit" :disabled="loading"
                                                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-70 disabled:cursor-not-allowed text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                    <x-button-spinner x-show="loading" x-cloak />
                                                    <span x-text="loading ? 'Mengirim...' : 'Kirim penolakan'"></span>
                                                </button>
                                                <button type="button" @click="tolak = false"
                                                    class="bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endcan
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>