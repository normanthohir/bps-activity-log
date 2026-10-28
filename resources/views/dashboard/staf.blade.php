{{-- resources/views/dashboard/staf.blade.php (mode Anggota) --}}
@php
    $user = auth()->user();
    $akhirPekan = today()->isWeekend();
    $bisaKetua = $timDipimpin->isNotEmpty();
    $persenLapor = $hariKerja > 0 ? min(100, round(($hariLapor / $hariKerja) * 100)) : 0;

    $kartu = [
        [
            'Tugas aktif',
            $tugasAktif,
            'belum selesai',
            'bg-blue-50 text-blue-600',
            'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z',
            route('staf.tugas.index'),
            null,
        ],
        [
            'Menunggu persetujuan',
            $menungguApproval,
            'laporan saya',
            'bg-amber-50 text-amber-600',
            'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            route('laporan.index'),
            null,
        ],
        [
            'Laporan bulan ini',
            $laporanBulanIni,
            $disetujuiBulanIni . ' disetujui',
            'bg-green-50 text-green-600',
            'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            route('laporan.index'),
            null,
        ],
        [
            'Kehadiran lapor',
            $hariLapor . '/' . $hariKerja,
            'hari kerja bulan ini',
            'bg-indigo-50 text-indigo-600',
            'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            route('laporan.index'),
            $persenLapor,
        ],
    ];
@endphp

<x-app-layout>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div
            class="relative overflow-hidden bg-gradient-to-br from-[#1F3864] to-[#2A4A82] rounded-2xl p-6 sm:p-8 mb-6 text-white animate-in">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @if ($bisaKetua)
                            <span
                                class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-sky-400/20 text-sky-100 ring-1 ring-inset ring-sky-300/30">
                                Mode Anggota
                            </span>
                        @endif
                        <span class="text-white/60 text-xs">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <h1 class="text-2xl font-semibold">Halo, {{ $user->name }}</h1>
                    <p class="text-white/70 text-sm mt-1">
                        {{ $user->bagian?->nama_bagian ?? 'Belum tergabung dalam tim' }}</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('laporan.create') }}"
                        class="inline-flex items-center justify-center gap-1.5 bg-white text-[#1F3864] hover:bg-white/90 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat laporan
                    </a>
                </div>
            </div>

            @if ($bisaKetua)
                <p class="relative mt-4 text-xs text-white/60">
                    Anda memimpin {{ $timDipimpin->implode(', ') }}. Persetujuan laporan dan tugas tim ada di mode Ketua
                    Tim.
                </p>
            @endif
        </div>

        {{-- Laporan dikembalikan --}}
        @if ($dikembalikan > 0)
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 animate-in">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm font-semibold text-red-900">{{ $dikembalikan }} laporan dikembalikan dan perlu
                        direvisi</p>
                </div>
                <ul class="space-y-2">
                    @foreach ($laporanDikembalikan as $l)
                        <li class="flex items-center justify-between gap-3 bg-white/70 rounded-lg px-3 py-2">
                            <div class="min-w-0">
                                <p class="text-sm text-red-900 truncate">{{ $l->tanggal->format('d M Y') }} &middot;
                                    {{ Str::limit($l->uraian, 50) }}</p>
                                <p class="text-xs text-red-700 truncate">Catatan:
                                    {{ $l->logApproval->last()?->catatan ?? '-' }}</p>
                            </div>
                            <a href="{{ route('laporan.edit', $l) }}"
                                class="shrink-0 text-xs font-semibold text-red-700 bg-white border border-red-200 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">Revisi</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('dashboard.partials.kalender-pekerjaan')

        {{-- Statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ($kartu as $i => [$label, $nilai, $sub, $warna, $ikon, $tautan, $bar])
                <a href="{{ $tautan }}"
                    class="animate-in delay-{{ $i + 1 }} bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center {{ $warna }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                        </svg>
                    </span>
                    <p class="text-2xl font-semibold text-gray-900 mt-3">{{ $nilai }}</p>
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $sub }}</p>
                    @if (!is_null($bar))
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mt-2.5">
                            <div class="h-full rounded-full bg-[#1F3864] animate-bar"
                                style="width: {{ $bar }}%"></div>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Tugas aktif --}}
            <div class="lg:col-span-3 animate-in delay-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Tugas aktif saya</h2>
                    <a href="{{ route('staf.tugas.index') }}"
                        class="text-xs font-medium text-[#1F3864] hover:underline">Lihat semua</a>
                </div>

                @if ($tugasAktifList->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-12 px-4">Tidak ada tugas aktif. Kerja bagus!</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($tugasAktifList as $t)
                            @php
                                $terlambat = $t->tenggat && $t->tenggat->isPast() && !$t->tenggat->isToday();
                                $dikerjakan = $t->status === 'dikerjakan';
                            @endphp
                            <a href="{{ route('staf.tugas.show', $t) }}"
                                class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-gray-50/70 transition-colors group">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate group-hover:text-[#1F3864]">
                                        {{ $t->judul }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        dari {{ $t->pemberiTugas?->name ?? '-' }}
                                        @if ($t->tenggat)
                                            &middot; <span
                                                class="{{ $terlambat ? 'text-red-600 font-medium' : '' }}">tenggat
                                                {{ $t->tenggat->format('d M Y') }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset
                                    {{ $dikerjakan ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full {{ $dikerjakan ? 'bg-blue-500' : 'bg-gray-400' }}"></span>
                                    {{ $dikerjakan ? 'Dikerjakan' : 'Belum dikerjakan' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Laporan terbaru --}}
            <div class="lg:col-span-2 animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Laporan terbaru</h2>
                    <a href="{{ route('laporan.index') }}"
                        class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                </div>

                @if ($laporanTerbaru->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-12 px-4">Belum ada laporan.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($laporanTerbaru as $l)
                            <a href="{{ route('laporan.show', $l) }}"
                                class="block px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs text-gray-500">{{ $l->tanggal->format('d M Y') }}</p>
                                    <x-status-badge :status="$l->status" />
                                </div>
                                <p class="text-sm text-gray-900 truncate mt-1">{{ $l->uraian }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
