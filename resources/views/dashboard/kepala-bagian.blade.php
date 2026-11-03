{{-- resources/views/dashboard/kepala-bagian.blade.php (mode Ketua Tim) --}}
@php
    $user = auth()->user();
    $jumlahStaf = $staf->count();
    $sudahLapor = $jumlahStaf - $belumLapor->count();
    $persenLapor = $jumlahStaf > 0 ? round(($sudahLapor / $jumlahStaf) * 100) : 0;

    $totalTugasTim = $tugasTimSelesai + $tugasTimDikerjakan + $tugasTimBelum;
    $tugasAktifTim = $tugasTimDikerjakan + $tugasTimBelum;
    $pctSelesai = $totalTugasTim > 0 ? round(($tugasTimSelesai / $totalTugasTim) * 100) : 0;
    $pctDikerjakan = $totalTugasTim > 0 ? round(($tugasTimDikerjakan / $totalTugasTim) * 100) : 0;
    $pctBelum = $totalTugasTim > 0 ? max(0, 100 - $pctSelesai - $pctDikerjakan) : 0;

    $kartu = [
        [
            'label' => 'Anggota Tim',
            'nilai' => $jumlahStaf,
            'ket' => $akhirPekan ? 'Akhir pekan' : $sudahLapor . ' sudah lapor hari ini',
            'url' => route('dashboard'),
            'garis' => 'from-blue-500 to-sky-400',
            'ikon' => 'from-blue-500 to-blue-600 shadow-blue-500/30',
            'blob' => 'bg-blue-50',
            'hover' => 'hover:border-blue-300',
            'angka' => 'text-gray-900',
            'svg' =>
                'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
        ],
        [
            'label' => 'Menunggu Persetujuan',
            'nilai' => $menungguApproval,
            'ket' => $menungguApproval > 0 ? 'Laporan tim perlu diproses' : 'Semua sudah diproses',
            'url' => route('kabag.approval.index', ['status' => 'menunggu']),
            'garis' => 'from-amber-500 to-yellow-300',
            'ikon' => 'from-amber-400 to-amber-500 shadow-amber-500/30',
            'blob' => 'bg-amber-50',
            'hover' => 'hover:border-amber-300',
            'angka' => $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900',
            'svg' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            'aktif' => $menungguApproval > 0,
        ],
        [
            'label' => 'Tingkat Persetujuan',
            'nilai' => $tingkatPersetujuan !== null ? $tingkatPersetujuan . '%' : '-',
            'ket' => 'Bulan ini',
            'url' => route('kabag.approval.index', ['periode' => now()->format('Y-m')]),
            'garis' => 'from-green-500 to-emerald-300',
            'ikon' => 'from-green-500 to-green-600 shadow-green-500/30',
            'blob' => 'bg-green-50',
            'hover' => 'hover:border-green-300',
            'angka' => 'text-gray-900',
            'svg' =>
                'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
        ],
        [
            'label' => 'Pekerjaan Tim Aktif',
            'nilai' => $tugasAktifTim,
            'ket' => $tugasTerlambat > 0 ? $tugasTerlambat . ' terlambat' : 'Tidak ada yang terlambat',
            'url' => route('pekerjaan.index'),
            'garis' => $tugasTerlambat > 0 ? 'from-red-500 to-rose-300' : 'from-indigo-500 to-violet-300',
            'ikon' =>
                $tugasTerlambat > 0
                    ? 'from-red-500 to-red-600 shadow-red-500/30'
                    : 'from-indigo-500 to-indigo-600 shadow-indigo-500/30',
            'blob' => $tugasTerlambat > 0 ? 'bg-red-50' : 'bg-indigo-50',
            'hover' => $tugasTerlambat > 0 ? 'hover:border-red-300' : 'hover:border-indigo-300',
            'angka' => 'text-gray-900',
            'svg' =>
                'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z',
        ],
    ];
@endphp

<x-app-layout>

    <style>
        @keyframes bps-up {
            from {
                opacity: 0;
                transform: translateY(18px)
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
                box-shadow: 0 0 0 0 rgb(245 158 11 / .55)
            }

            100% {
                box-shadow: 0 0 0 12px rgb(245 158 11 / 0)
            }
        }

        @keyframes bps-float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-6px)
            }
        }

        @keyframes bps-shine {
            from {
                background-position: -200% 0
            }

            to {
                background-position: 200% 0
            }
        }

        @keyframes bps-pop {
            0% {
                transform: scale(.6);
                opacity: 0
            }

            70% {
                transform: scale(1.08)
            }

            100% {
                transform: scale(1);
                opacity: 1
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

        .bps .fx {
            opacity: 0;
            animation: bps-up .6s cubic-bezier(.2, .7, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        .bps .fx-bar {
            transform-origin: left;
            animation: bps-grow 1s .5s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .fx-pop {
            animation: bps-pop .5s cubic-bezier(.2, .7, .2, 1) both;
            animation-delay: var(--d, 0s);
        }

        .bps .fx-ring {
            animation: bps-spin .9s .4s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .ping {
            animation: bps-ping 1.8s ease-out infinite;
        }

        .bps .float {
            animation: bps-float 4s ease-in-out infinite;
        }

        .bps .shine {
            background: linear-gradient(110deg, transparent 30%, rgb(255 255 255 / .8) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: bps-shine 3s linear infinite;
        }

        .bps .stat {
            transition: transform .3s, box-shadow .3s, border-color .3s;
        }

        .bps .stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px -14px rgb(31 56 100 / .35);
        }

        .bps .stat .blob {
            transition: transform .6s;
        }

        .bps .stat:hover .blob {
            transform: scale(1.8);
        }

        .bps .stat .icon {
            transition: transform .35s;
        }

        .bps .stat:hover .icon {
            transform: rotate(-8deg) scale(1.12);
        }

        /* Kalender: hanya mempercantik tampilan, logika tidak diubah */
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
            .bps .fx-pop,
            .bps .fx-ring {
                opacity: 1;
            }
        }
    </style>

    <div class="bps max-w-6xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div class="fx relative overflow-hidden bg-gradient-to-br from-[#14264a] via-[#1F3864] to-[#355b91] rounded-2xl p-5 sm:p-8 mb-5 text-white"
            style="--d:0s">
            <div class="absolute -top-10 -right-10 w-44 h-44 rounded-full bg-white/10 float"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-100 ring-1 ring-inset ring-amber-300/30">
                            Mode Ketua Tim
                        </span>
                        <span class="text-white/60 text-xs">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold">Halo, {{ $user->name }}</h1>
                    <p class="text-white/70 text-sm mt-1">
                        {{ $namaTim ?: $bagian?->nama_bagian ?? 'Belum memimpin tim' }} &middot; {{ $jumlahStaf }}
                        anggota
                    </p>
                    <p class="text-white/80 text-sm mt-2">
                        @if ($menungguApproval > 0)
                            Ada <span class="font-bold text-white">{{ $menungguApproval }} laporan</span> tim menunggu
                            persetujuan Anda.
                        @else
                            Semua laporan tim sudah diproses.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-action-button href="{{ route('pekerjaan.create') }}" variant="light">Pekerjaan / disposisi
                        baru</x-action-button>
                    @if ($menungguApproval > 0)
                        <a href="{{ route('kabag.approval.index', ['status' => 'menunggu']) }}"
                            class="border border-white/60 rounded-lg px-4 py-2 text-sm text-white hover:bg-white/10 hover:-translate-y-0.5 transition">
                            Proses laporan →
                        </a>
                    @endif
                </div>
            </div>

            <p class="relative mt-4 text-xs text-white/60">
                Laporan harian pribadi dan pekerjaan yang diberikan kepada Anda diisi di mode Anggota.
            </p>
        </div>

        {{-- Perlu tindakan --}}
        @if ($menungguApproval > 0 || $tugasTerlambat > 0)
            <div class="fx mb-5 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4"
                style="--d:.1s">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-lg bg-amber-400 text-white flex items-center justify-center ping">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </span>
                    <p class="text-sm font-semibold text-amber-900">Perlu tindakan Anda</p>
                </div>
                <ul class="space-y-1.5 text-sm">
                    @if ($menungguApproval > 0)
                        <li class="group flex flex-wrap items-center justify-between gap-2">
                            <span class="text-amber-900"><b>{{ $menungguApproval }}</b> laporan tim menunggu
                                persetujuan.</span>
                            <a href="{{ route('kabag.approval.index', ['status' => 'menunggu']) }}"
                                class="text-xs font-semibold text-amber-800 hover:underline">Proses sekarang →</a>
                        </li>
                    @endif
                    @if ($tugasTerlambat > 0)
                        <li class="group flex flex-wrap items-center justify-between gap-2">
                            <span class="text-amber-900"><b>{{ $tugasTerlambat }}</b> pekerjaan tim sudah melewati
                                tenggat.</span>
                            <a href="{{ route('pekerjaan.index', ['terlambat' => 1]) }}"
                                class="text-xs font-semibold text-amber-800 hover:underline">Lihat pekerjaan →</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        {{-- Statistik --}}
        <div class="fx flex items-center justify-between mb-3" style="--d:.15s">
            <h2 class="text-sm font-semibold text-gray-900">Ringkasan Tim</h2>
            <span
                class="text-xs font-medium text-[#1F3864] bg-[#DCE6F1] rounded-full px-3 py-1">{{ now()->translatedFormat('F Y') }}</span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            @foreach ($kartu as $i => $k)
                <a href="{{ $k['url'] }}"
                    class="fx stat group relative block overflow-hidden bg-white border border-gray-200 {{ $k['hover'] }} rounded-2xl p-4 sm:p-5"
                    style="--d:{{ 0.2 + $i * 0.08 }}s">
                    <span class="blob absolute -right-6 -bottom-6 w-24 h-24 rounded-full {{ $k['blob'] }}"></span>
                    <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $k['garis'] }}"></span>
                    @if (!empty($k['aktif']))
                        <span class="shine absolute inset-x-0 top-0 h-1"></span>
                    @endif

                    <div class="relative">
                        <div
                            class="icon w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br {{ $k['ikon'] }} text-white shadow-md
                                    flex items-center justify-center {{ !empty($k['aktif']) ? 'ping' : '' }}">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['svg'] }}" />
                            </svg>
                        </div>
                        <p class="fx-pop text-3xl sm:text-4xl font-bold tabular-nums mt-3 leading-none {{ $k['angka'] }}"
                            style="--d:{{ 0.4 + $i * 0.08 }}s">{{ $k['nilai'] }}</p>
                        <p class="text-sm font-medium text-gray-700 mt-1.5 truncate">{{ $k['label'] }}</p>
                        <p class="text-[11px] sm:text-xs text-gray-400 truncate">{{ $k['ket'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Kalender --}}
        <div class="fx" style="--d:.5s">
            @include('dashboard.partials.kalender-pekerjaan')
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Kolom kiri --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Laporan hari ini --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl p-4 sm:p-5" style="--d:.55s">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900">Laporan hari ini</h2>
                        @unless ($akhirPekan)
                            <span class="text-xs text-gray-500 bg-gray-100 rounded-full px-2.5 py-1">{{ $sudahLapor }}
                                dari {{ $jumlahStaf }} anggota</span>
                        @endunless
                    </div>

                    @if ($akhirPekan)
                        <p class="text-sm text-gray-400 py-2 text-center">Hari ini akhir pekan, tidak ada kewajiban
                            lapor.</p>
                    @elseif ($jumlahStaf === 0)
                        <p class="text-sm text-gray-400 py-2 text-center">Belum ada anggota di tim Anda.</p>
                    @else
                        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                            {{-- Cincin persentase (CSS murni) --}}
                            <div class="fx-ring relative w-24 h-24 shrink-0 mx-auto sm:mx-0 rounded-full"
                                style="background: conic-gradient(#22c55e {{ $persenLapor }}%, #e5e7eb 0)">
                                <div
                                    class="absolute inset-2 rounded-full bg-white flex flex-col items-center justify-center">
                                    <span
                                        class="text-xl font-bold text-gray-900 tabular-nums leading-none">{{ $persenLapor }}%</span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">sudah lapor</span>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                @if ($belumLapor->isEmpty())
                                    <p
                                        class="flex items-center gap-2 text-sm text-green-700 bg-green-50 rounded-xl px-3 py-2.5">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Semua anggota sudah mengirim laporan hari ini.
                                    </p>
                                @else
                                    <p class="text-xs font-medium text-gray-500 mb-2">Belum melapor
                                        ({{ $belumLapor->count() }})</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($belumLapor->take(8) as $n => $p)
                                            <span
                                                class="fx inline-flex items-center gap-2 bg-gray-50 border border-gray-200 hover:border-amber-300 hover:bg-amber-50/50 transition rounded-full pl-1 pr-3 py-1 text-xs text-gray-700"
                                                style="--d:{{ 0.7 + $n * 0.04 }}s">
                                                <span
                                                    class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-[11px] font-semibold flex items-center justify-center">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                                {{ $p->name }}
                                            </span>
                                        @endforeach
                                        @if ($belumLapor->count() > 8)
                                            <span
                                                class="inline-flex items-center text-xs text-gray-500 px-2">+{{ $belumLapor->count() - 8 }}
                                                lainnya</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tugas tim terbaru --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.7s">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Pekerjaan tim terbaru</h2>
                        <a href="{{ route('pekerjaan.index') }}"
                            class="group text-xs font-medium text-[#1F3864] inline-flex items-center gap-1">
                            Lihat semua <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>

                    @if ($tugasTimTerbaru->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-10 px-4">Belum ada pekerjaan yang diberikan.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasTimTerbaru as $i => $tugas)
                                @php
                                    $badge = match ($tugas->status) {
                                        'selesai' => [
                                            'bg-green-50 text-green-700 ring-green-600/20',
                                            'bg-green-500',
                                            'Selesai',
                                        ],
                                        'dikerjakan' => [
                                            'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            'bg-blue-500',
                                            'Dikerjakan',
                                        ],
                                        default => [
                                            'bg-gray-100 text-gray-600 ring-gray-500/20',
                                            'bg-gray-400',
                                            'Belum',
                                        ],
                                    };
                                @endphp
                                <a href="{{ route('kabag.tugas.show', $tugas) }}"
                                    class="fx group flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-blue-50/40 transition-colors"
                                    style="--d:{{ 0.75 + $i * 0.05 }}s">
                                    <div class="min-w-0">
                                        <p
                                            class="font-medium text-gray-900 text-sm truncate group-hover:text-[#1F3864]">
                                            {{ $tugas->judul }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">
                                            {{ $tugas->penerimaTugas?->name ?? '-' }}
                                            @if ($tugas->tenggat)
                                                &middot; tenggat {{ $tugas->tenggat->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1.5 {{ $badge[0] }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset shrink-0">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>{{ $badge[2] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom kanan --}}
            <div class="space-y-5">

                {{-- Progress kerja tim --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl p-4 sm:p-5" style="--d:.62s">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900">Progres Pekerjaan tim</h2>
                        <span class="text-xs text-gray-500">{{ $totalTugasTim }} Tugas</span>
                    </div>

                    @if ($totalTugasTim > 0)
                        <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden flex">
                            <div class="fx-bar h-full bg-gradient-to-r from-green-500 to-green-400"
                                style="width: {{ $pctSelesai }}%"></div>
                            <div class="fx-bar h-full bg-gradient-to-r from-blue-500 to-blue-400"
                                style="width: {{ $pctDikerjakan }}%"></div>
                            <div class="fx-bar h-full bg-gray-300" style="width: {{ $pctBelum }}%"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-4">
                            <div class="rounded-xl bg-green-50 px-3 py-2.5 transition hover:-translate-y-0.5">
                                <p class="text-xl font-bold text-green-700 tabular-nums">{{ $tugasTimSelesai }}</p>
                                <p class="text-[11px] sm:text-xs text-green-700/80">Selesai · {{ $pctSelesai }}%</p>
                            </div>
                            <div class="rounded-xl bg-blue-50 px-3 py-2.5 transition hover:-translate-y-0.5">
                                <p class="text-xl font-bold text-blue-700 tabular-nums">{{ $tugasTimDikerjakan }}</p>
                                <p class="text-[11px] sm:text-xs text-blue-700/80">Dikerjakan · {{ $pctDikerjakan }}%
                                </p>
                            </div>
                            <div class="rounded-xl bg-gray-100 px-3 py-2.5 transition hover:-translate-y-0.5">
                                <p class="text-xl font-bold text-gray-700 tabular-nums">{{ $tugasTimBelum }}</p>
                                <p class="text-[11px] sm:text-xs text-gray-500">Belum · {{ $pctBelum }}%</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-2 text-center">Belum ada Pekerjaan yang diberikan ke tim.
                        </p>
                    @endif
                </div>

                {{-- Menunggu persetujuan --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.6s">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                            Menunggu persetujuan
                            @if ($laporanMenunggu->isNotEmpty())
                                <span class="w-2 h-2 rounded-full bg-amber-500 ping"></span>
                            @endif
                        </h2>
                        <a href="{{ route('kabag.approval.index') }}"
                            class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($laporanMenunggu->isEmpty())
                        <div class="text-center py-8 px-4">
                            <span
                                class="float inline-flex w-10 h-10 rounded-full bg-green-50 text-green-600 items-center justify-center">✓</span>
                            <p class="text-sm text-gray-400 mt-2">Tidak ada laporan menunggu.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($laporanMenunggu as $i => $laporan)
                                <a href="{{ route('kabag.approval.show', $laporan) }}"
                                    class="fx group flex items-center gap-2.5 px-5 py-3 hover:bg-amber-50/40 transition-colors"
                                    style="--d:{{ 0.65 + $i * 0.06 }}s">
                                    <span
                                        class="w-8 h-8 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0 transition group-hover:scale-110">
                                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $laporan->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $laporan->tanggal->format('d M Y') }}</p>
                                    </div>
                                    <span
                                        class="text-gray-300 transition group-hover:translate-x-1 group-hover:text-[#1F3864]">→</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
