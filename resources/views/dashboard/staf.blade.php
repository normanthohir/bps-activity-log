{{-- resources/views/dashboard/staf.blade.php (mode Anggota) --}}
@php
    $user = auth()->user();
    $akhirPekan = today()->isWeekend();
    $bisaKetua = $timDipimpin->isNotEmpty();
    $persenLapor = $hariKerja > 0 ? min(100, round(($hariLapor / $hariKerja) * 100)) : 0;
    $pctDisetujui = $laporanBulanIni > 0 ? min(100, round(($disetujuiBulanIni / $laporanBulanIni) * 100)) : 0;
@endphp

<x-app-layout>

    <style>
        @keyframes bps-up {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes bps-grow {
            from {
                transform: scaleX(0)
            }

            to {
                transform: scaleX(1)
            }
        }

        @keyframes bps-ping {
            0% {
                box-shadow: 0 0 0 0 rgb(239 68 68 / .5)
            }

            100% {
                box-shadow: 0 0 0 10px rgb(239 68 68 / 0)
            }
        }

        @keyframes bps-spin {
            from {
                transform: rotate(-90deg) scale(.7);
                opacity: 0
            }

            to {
                transform: rotate(0) scale(1);
                opacity: 1
            }
        }

        @keyframes bps-pop {
            0% {
                transform: scale(.7);
                opacity: 0
            }

            70% {
                transform: scale(1.06)
            }

            100% {
                transform: scale(1);
                opacity: 1
            }
        }

        .bps .fx {
            opacity: 0;
            animation: bps-up .55s cubic-bezier(.2, .7, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        .bps .fx-bar {
            transform-origin: left;
            animation: bps-grow .9s .35s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .fx-ring {
            animation: bps-spin .9s .3s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .fx-pop {
            animation: bps-pop .5s cubic-bezier(.2, .7, .2, 1) both;
            animation-delay: var(--d, 0s);
        }

        .bps .ping {
            animation: bps-ping 1.8s ease-out infinite;
        }

        .bps .lift {
            transition: transform .25s, box-shadow .25s, border-color .25s;
        }

        .bps .lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -14px rgb(31 56 100 / .35);
        }

        /* Kalender: hanya tampilan, logika tidak diubah */
        .bps .rapi-calendar-day:not(.outside) {
            transition: background .2s;
        }

        .bps .rapi-calendar-day:not(.outside):hover {
            background: #f1f5f9;
        }

        .bps .rapi-calendar-day.outside {
            background: repeating-linear-gradient(135deg, #f8fafc, #f8fafc 6px, #f1f5f9 6px, #f1f5f9 12px);
        }

        .bps .rapi-calendar-date.today {
            background: linear-gradient(135deg, #1f3864, #355b91);
            box-shadow: 0 4px 10px -2px rgb(31 56 100 / .5);
        }

        .bps .rapi-calendar-event {
            border-radius: 7px;
            box-shadow: 0 1px 2px rgb(15 23 42 / .08);
            transition: transform .2s, box-shadow .2s;
        }

        .bps .rapi-calendar-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px -4px rgb(15 23 42 / .25);
            text-decoration: none;
            filter: none;
        }

        .bps .rapi-calendar-event.belum_dikerjakan {
            background: linear-gradient(90deg, #fde68a, #fef3c7);
        }

        .bps .rapi-calendar-event.dikerjakan {
            background: linear-gradient(90deg, #bfdbfe, #dbeafe);
        }

        .bps .rapi-calendar-event.selesai {
            background: linear-gradient(90deg, #a7f3d0, #d1fae5);
        }

        @media (prefers-reduced-motion: reduce) {

            .bps *,
            .bps *::before {
                animation: none !important;
                transition: none !important;
            }

            .bps .fx,
            .bps .fx-ring,
            .bps .fx-pop {
                opacity: 1;
            }
        }
    </style>

    <div class="bps max-w-6xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero: kiri sapaan, kanan cincin kehadiran lapor --}}
        <div class="fx relative overflow-hidden rounded-2xl bg-[#1F3864] text-white p-5 sm:p-7 mb-5"
            style="--d:0s; background-image: radial-gradient(120% 90% at 100% 0%, rgb(53 91 145 / .7), transparent 60%)">
            <div class="relative grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
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
                    <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight leading-tight">Halo, {{ $user->name }}
                    </h1>
                    <p class="text-white/70 text-sm mt-1.5">
                        {{ $user->bagian?->nama_bagian ?? 'Belum tergabung dalam tim' }}
                        @if ($akhirPekan)
                            &middot; Hari ini akhir pekan
                        @endif
                    </p>

                    <div class="flex flex-wrap gap-2 mt-5">
                        <a href="{{ route('laporan.create') }}"
                            class="group inline-flex items-center justify-center gap-1.5 bg-white text-[#1F3864] hover:bg-white/90 hover:-translate-y-0.5 text-sm font-semibold px-4 py-2.5 rounded-lg transition">
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Buat laporan
                        </a>
                        <a href="{{ route('laporan.index') }}"
                            class="border border-white/50 rounded-lg px-4 py-2.5 text-sm text-white hover:bg-white/10 transition">
                            Riwayat laporan
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-white/10 ring-1 ring-white/15 px-5 py-4">
                    <div class="fx-ring relative w-20 h-20 shrink-0 rounded-full"
                        style="background: conic-gradient(#4ade80 {{ $persenLapor }}%, rgb(255 255 255 / .18) 0)">
                        <div class="absolute inset-1.5 rounded-full bg-[#22407a] flex items-center justify-center">
                            <span class="text-lg font-bold tabular-nums">{{ $persenLapor }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-white/70">Kehadiran lapor</p>
                        <p class="text-2xl font-bold tabular-nums tracking-tight leading-none mt-1">
                            {{ $hariLapor }}<span
                                class="text-white/50 text-base font-medium">/{{ $hariKerja }}</span></p>
                        <p class="text-[11px] text-white/60 mt-1">hari kerja bulan ini</p>
                    </div>
                </div>
            </div>

            @if ($bisaKetua)
                <p class="relative mt-5 text-xs text-white/60">
                    Anda memimpin {{ $timDipimpin->implode(', ') }}. Persetujuan laporan dan pekerjaan tim ada di mode
                    Ketua Tim.
                </p>
            @endif
        </div>

        {{-- Kalender --}}
        <div class="fx" style="--d:.12s">
            @include('dashboard.partials.kalender-pekerjaan')
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- Pekerjaan aktif --}}
            <div class="fx lg:col-span-3 bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.2s">
                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <h2 class="font-semibold text-gray-900">Pekerjaan aktif saya</h2>

                        {{-- Lencana total: tooltip muncul saat kursor diarahkan atau saat disentuh --}}
                        <span tabindex="0" class="group relative inline-flex cursor-help outline-none">
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-blue-50 px-2 text-xs font-bold tabular-nums text-blue-700 ring-1 ring-inset ring-blue-600/20 transition group-hover:bg-blue-600 group-hover:text-white group-focus:bg-blue-600 group-focus:text-white motion-reduce:transition-none">
                                {{ $tugasAktif }}
                            </span>
                            <span role="tooltip"
                                class="pointer-events-none invisible absolute left-1/2 top-full z-20 mt-2 -translate-x-1/2 -translate-y-1 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus:visible group-focus:translate-y-0 group-focus:opacity-100 motion-reduce:transition-none">
                                <span
                                    class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900"></span>
                                Total {{ $tugasAktif }} pekerjaan aktif, belum selesai
                            </span>
                        </span>
                    </div>

                    <a href="{{ route('pekerjaan.index') }}"
                        class="group text-xs font-medium text-[#1F3864] inline-flex items-center gap-1">
                        Lihat semua <span
                            class="transition-transform group-hover:translate-x-1 motion-reduce:transition-none">→</span>
                    </a>
                </div>

                @if ($tugasAktifList->isEmpty())
                    <div class="text-center py-12 px-4">
                        <span
                            class="inline-flex w-10 h-10 rounded-full bg-green-50 text-green-600 items-center justify-center">✓</span>
                        <p class="text-sm text-gray-400 mt-2">Tidak ada pekerjaan aktif.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($tugasAktifList as $i => $t)
                            @php
                                $terlambat = $t->tenggat && $t->tenggat->isPast() && !$t->tenggat->isToday();
                                $dikerjakan = $t->status === 'dikerjakan';
                            @endphp
                            <a href="{{ route('pekerjaan.show', $t) }}"
                                class="fx group flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-blue-50/40 transition-colors"
                                style="--d:{{ 0.3 + $i * 0.05 }}s">
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
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if ($terlambat)
                                        <span
                                            class="text-[10px] font-semibold text-red-700 bg-red-50 ring-1 ring-inset ring-red-600/20 px-2 py-0.5 rounded-full">Terlambat</span>
                                    @endif
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset
                                {{ $dikerjakan ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $dikerjakan ? 'bg-blue-500' : 'bg-gray-400' }}"></span>
                                        {{ $dikerjakan ? 'Dikerjakan' : 'Belum' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Laporan terbaru + ringkasan --}}
            <div class="fx lg:col-span-2 bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.28s">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Laporan terbaru</h2>
                    <a href="{{ route('laporan.index') }}"
                        class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                </div>

                {{-- Ringkasan: menunggu persetujuan + laporan bulan ini --}}
                <div class="grid grid-cols-2 divide-x divide-gray-100 border-b border-gray-100 bg-gray-50/60">
                    <a href="{{ route('laporan.index', ['status' => 'menunggu']) }}"
                        class="group px-5 py-3.5 transition-colors hover:bg-amber-50/60">
                        <p class="flex items-center gap-1.5 text-xs text-gray-500">
                            @if ($menungguApproval > 0)
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 animate-ping motion-reduce:animate-none"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                                </span>
                            @endif
                            Menunggu persetujuan
                        </p>
                        <p class="fx-pop mt-1 text-2xl font-bold tabular-nums tracking-tight leading-none {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}"
                            style="--d:.45s">
                            {{ $menungguApproval }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400 group-hover:text-amber-700">laporan saya →</p>
                    </a>

                    <a href="{{ route('laporan.index', ['periode' => now()->format('Y-m')]) }}"
                        class="group px-5 py-3.5 transition-colors hover:bg-green-50/60">
                        <p class="text-xs text-gray-500">Laporan {{ now()->translatedFormat('F') }}</p>
                        <p class="fx-pop mt-1 flex items-baseline gap-1 text-2xl font-bold tabular-nums tracking-tight leading-none text-gray-900"
                            style="--d:.5s">
                            {{ $laporanBulanIni }}
                            <span class="text-xs font-medium text-gray-400">{{ $disetujuiBulanIni }} disetujui</span>
                        </p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200">
                            <div class="fx-bar h-full rounded-full bg-green-500" style="width: {{ $pctDisetujui }}%">
                            </div>
                        </div>
                    </a>
                </div>

                @if ($laporanTerbaru->isEmpty())
                    <div class="text-center py-12 px-4">
                        <p class="text-sm text-gray-400">Belum ada laporan.</p>
                        <a href="{{ route('laporan.create') }}"
                            class="inline-block mt-3 text-sm font-medium text-[#1F3864] hover:underline">Buat laporan
                            pertama</a>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($laporanTerbaru as $i => $l)
                            <a href="{{ route('laporan.show', $l) }}"
                                class="fx group block px-5 py-3 hover:bg-gray-50/70 transition-colors"
                                style="--d:{{ 0.4 + $i * 0.05 }}s">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs text-gray-500">{{ $l->tanggal->format('d M Y') }}</p>
                                    <x-status-badge :status="$l->status" />
                                </div>
                                <p class="text-sm text-gray-900 truncate mt-1 group-hover:text-[#1F3864]">
                                    {{ $l->uraian }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
