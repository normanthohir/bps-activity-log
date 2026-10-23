{{-- resources/views/tugas/show-staf.blade.php (detail tugas aktif, dipakai staf dan kepala tim) --}}
@php
    $user = auth()->user();
    $isStaf = $user->role === 'staf';

    // Route mengikuti role, supaya tombol tidak mengarah ke halaman yang dilarang
    $routeKembali = $isStaf ? 'staf.tugas.index' : 'kabag.tugas-aktif.index';
    $routeBuat = $isStaf ? 'laporan.create' : 'kabag.laporan.create';
    $routeEdit = $isStaf ? 'laporan.edit' : 'kabag.laporan.edit';
    $routeLihat = $isStaf ? 'laporan.show' : 'kabag.laporan.show';

    $belumDikerjakan = $tugas->status === 'belum_dikerjakan';

    $terlambat = $tugas->tenggat && $tugas->tenggat->isPast() && ! $tugas->tenggat->isToday()
        && $tugas->status !== 'selesai';

    [$statusLabel, $statusGaya] = match ($tugas->status) {
        'selesai' => ['Selesai', 'bg-green-50 text-green-700 ring-green-600/20'],
        'dikerjakan' => ['Dikerjakan', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        default => ['Belum dikerjakan', 'bg-gray-50 text-gray-600 ring-gray-500/20'],
    };
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
        <a href="{{ route($routeKembali) }}"
            class="animate-in inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke tugas aktif
        </a>

        {{-- Judul tugas + tombol buat laporan --}}
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

            @if ($belumDikerjakan)
                <a href="{{ route($routeBuat, ['tugas' => $tugas->id]) }}"
                    class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors self-start shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat laporan
                </a>
            @endif
        </div>

        {{-- Ringkasan: tenggat, pemberi, bagian --}}
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
                <p class="text-xs text-gray-500 mb-1">Bagian</p>
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $tugas->bagian?->nama_bagian ?? '-' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Dibuat {{ $tugas->created_at->format('d M Y') }}</p>
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

        {{-- Riwayat laporan --}}
        <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Riwayat laporan saya</p>
            </div>

            @if ($tugas->laporanHarian->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan</p>
                    <p class="text-sm text-gray-500 mt-1">Mulai buat laporan untuk tugas ini.</p>
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

                            {{-- Catatan penolakan + tombol revisi --}}
                            @if ($laporan->status === 'dikembalikan')
                                <div class="mt-2 bg-red-50 text-red-700 text-xs px-3 py-2 rounded-lg">
                                    <span class="font-medium">Dikembalikan:</span>
                                    {{ $logTerakhir?->catatan ?? '-' }}
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
                                <a href="{{ route($routeLihat, $laporan) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-[#1F3864] hover:underline">
                                    Lihat detail
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>

                                @can('update', $laporan)
                                    <a href="{{ route($routeEdit, $laporan) }}"
                                        class="text-xs font-medium {{ $laporan->status === 'dikembalikan' ? 'text-red-600' : 'text-gray-600' }} hover:underline">
                                        {{ $laporan->status === 'dikembalikan' ? 'Revisi laporan ini' : 'Edit draft' }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>