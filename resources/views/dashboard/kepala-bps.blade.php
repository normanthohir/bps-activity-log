{{-- resources/views/dashboard/kepala-bps.blade.php --}}
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
                box-shadow: 0 0 0 0 rgb(245 158 11 / .55)
            }

            100% {
                box-shadow: 0 0 0 10px rgb(245 158 11 / 0)
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

            .bps .fx {
                opacity: 1;
            }
        }
    </style>

    @php
        $periode = now()->format('Y-m');
        $bulanIni = now()->translatedFormat('F Y');

        $sisaMenunggu = max(0, $menungguApproval);
        $totalDilihat = $disetujuiBulanIni + $dikembalikanBulanIni + $sisaMenunggu;
        $pctDisetujui = $totalDilihat > 0 ? round(($disetujuiBulanIni / $totalDilihat) * 100) : 0;
        $pctDikembalikan = $totalDilihat > 0 ? round(($dikembalikanBulanIni / $totalDilihat) * 100) : 0;
        $pctMenunggu = $totalDilihat > 0 ? max(0, 100 - $pctDisetujui - $pctDikembalikan) : 0;

        $maksLaporan = max(1, (int) $rekapPerBagian->max('total_laporan_bulan_ini'));
    @endphp

    <div class="bps max-w-6xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero: kiri sapaan, kanan angka utama --}}
        <div class="fx relative overflow-hidden rounded-2xl bg-[#1F3864] text-white p-5 sm:p-7 mb-5"
            style="--d:0s; background-image: radial-gradient(120% 90% at 100% 0%, rgb(53 91 145 / .7), transparent 60%)">
            <div class="relative grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide mb-2">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight leading-tight">Kepala BPS Kota Ambon
                    </h1>
                    <p class="text-white/70 text-sm mt-1.5 max-w-md">
                        Persetujuan laporan dan pekerjaan dari seluruh tim.
                    </p>
                    <div class="flex flex-wrap gap-2 mt-5">
                        <x-action-button href="{{ route('pekerjaan.create') }}" variant="light">Beri
                            Pekerjaan</x-action-button>
                        <a href="{{ route('sop.index') }}"
                            class="border border-white/50 rounded-lg px-4 py-2 text-sm text-white hover:bg-white/10 transition">
                            Delegasikan SOP ke Ketua Tim
                        </a>
                    </div>
                </div>

                <div class="rounded-xl bg-white/10 ring-1 ring-white/15 px-5 py-4 lg:min-w-[220px]">
                    @if ($menungguApproval > 0)
                        <p class="flex items-center gap-2 text-xs text-white/70">
                            <span class="w-2 h-2 rounded-full bg-amber-400 ping"></span>
                            Menunggu persetujuan
                        </p>
                        <p class="text-5xl font-bold tabular-nums tracking-tight leading-none mt-2">
                            {{ $menungguApproval }}</p>
                        <a href="{{ route('kepala-bps.approval.index', ['status' => 'menunggu']) }}"
                            class="group inline-flex items-center gap-1 mt-3 text-sm font-medium text-white">
                            Proses sekarang
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    @else
                        <p class="text-xs text-white/70">Menunggu persetujuan</p>
                        <p class="text-5xl font-bold tabular-nums tracking-tight leading-none mt-2">0</p>
                        <p class="text-sm text-white/80 mt-3">Semua laporan sudah diproses.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kalender --}}
        <div class="fx" style="--d:.1s">
            @include('dashboard.partials.kalender-pekerjaan')
        </div>

        {{-- Ringkasan laporan bulan ini --}}
        <div class="fx flex items-center justify-between mb-3" style="--d:.15s">
            <h2 class="text-sm font-semibold text-gray-900">Ringkasan Laporan</h2>
            <span
                class="text-xs font-medium text-[#1F3864] bg-[#DCE6F1] rounded-full px-3 py-1">{{ $bulanIni }}</span>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Kolom kiri: rekap per tim --}}
            <div class="lg:col-span-2">

                {{-- Kartu utama: menunggu + progres --}}
                <div class="fx lift col-span-2 mb-3 relative overflow-hidden bg-white border border-gray-200 hover:border-amber-300 rounded-2xl p-4 sm:p-5"
                    style="--d:.2s">
                    <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-500 to-yellow-300"></span>

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Menunggu Persetujuan</p>
                            <p
                                class="text-4xl sm:text-5xl font-bold tabular-nums tracking-tight leading-none mt-2
                                  {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                                {{ $menungguApproval }}</p>
                            <p class="text-xs text-gray-400 mt-2">
                                dari {{ $totalLaporanBulanIni }} laporan masuk
                            </p>
                        </div>
                        <a href="{{ route('kepala-bps.approval.index', ['periode' => $periode]) }}"
                            class="group text-xs font-medium text-[#1F3864] inline-flex items-center gap-1 shrink-0">
                            Semua laporan
                            <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>

                    @if ($totalDilihat > 0)
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden flex mt-5">
                            <div class="fx-bar h-full bg-green-500" style="width: {{ $pctDisetujui }}%"></div>
                            <div class="fx-bar h-full bg-amber-400" style="width: {{ $pctMenunggu }}%"></div>
                            <div class="fx-bar h-full bg-red-400" style="width: {{ $pctDikembalikan }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5 text-[11px] sm:text-xs text-gray-500">
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2 h-2 rounded-full bg-green-500"></span>Disetujui
                                {{ $pctDisetujui }}%</span>
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2 h-2 rounded-full bg-amber-400"></span>Menunggu
                                {{ $pctMenunggu }}%</span>
                            <span class="flex items-center gap-1.5"><span
                                    class="w-2 h-2 rounded-full bg-red-400"></span>Dikembalikan
                                {{ $pctDikembalikan }}%</span>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 mt-5">Belum ada laporan bulan ini.</p>
                    @endif
                </div>

                <div class="fx bg-white rounded-2xl border border-gray-200 overflow-hidden" style="--d:.4s">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-2">
                        <h2 class="font-semibold text-gray-900">Rekap Pelaporan Per Tim</h2>
                        <a href="{{ route('kepala-bps.rekap') }}"
                            class="group text-sm text-[#1F3864] font-medium inline-flex items-center gap-1">
                            Lihat Detail <span class="transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>

                    {{-- Mobile: daftar --}}
                    <div class="md:hidden divide-y divide-gray-100">
                        @foreach ($rekapPerBagian as $i => $bagian)
                            <a href="{{ route('kepala-bps.approval.index', ['bagian' => $bagian->id]) }}"
                                class="fx block px-4 py-3 active:bg-gray-50"
                                style="--d:{{ 0.45 + min($i, 8) * 0.04 }}s">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $bagian->nama_bagian }}
                                    </p>
                                    @if ($bagian->menunggu_approval > 0)
                                        <span
                                            class="bg-amber-50 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full ring-1 ring-inset ring-amber-600/20 shrink-0">{{ $bagian->menunggu_approval }}
                                            menunggu</span>
                                    @endif
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden mt-2">
                                    <div class="fx-bar h-full rounded-full bg-[#355b91]"
                                        style="width: {{ round(($bagian->total_laporan_bulan_ini / $maksLaporan) * 100) }}%">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1.5">{{ $bagian->anggota_tim_count }} pegawai ·
                                    {{ $bagian->total_laporan_bulan_ini }} laporan</p>
                            </a>
                        @endforeach
                    </div>

                    {{-- Desktop: tabel --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                                    <th class="py-2.5 px-5 font-medium">Tim</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Pegawai</th>
                                    <th class="py-2.5 px-5 font-medium">Laporan bulan ini</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Menunggu</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($rekapPerBagian as $i => $bagian)
                                    <tr class="fx group hover:bg-blue-50/40 transition-colors"
                                        style="--d:{{ 0.45 + min($i, 8) * 0.04 }}s">
                                        <td class="py-3 px-5 font-medium text-gray-900">{{ $bagian->nama_bagian }}</td>
                                        <td class="py-3 px-5 text-right text-gray-500 tabular-nums">
                                            {{ $bagian->anggota_tim_count }}</td>
                                        <td class="py-3 px-5">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="w-8 text-gray-700 tabular-nums">{{ $bagian->total_laporan_bulan_ini }}</span>
                                                <div class="h-1.5 w-24 rounded-full bg-gray-100 overflow-hidden">
                                                    <div class="fx-bar h-full rounded-full bg-[#355b91]"
                                                        style="width: {{ round(($bagian->total_laporan_bulan_ini / $maksLaporan) * 100) }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            @if ($bagian->menunggu_approval > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-semibold px-2.5 py-0.5 rounded-full ring-1 ring-inset ring-amber-600/20">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $bagian->menunggu_approval }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs tabular-nums">0</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <a href="{{ route('kepala-bps.approval.index', ['bagian' => $bagian->id]) }}"
                                                class="inline-flex items-center gap-1 text-[#1F3864] text-xs font-medium">
                                                Lihat Laporan
                                                <span class="transition-transform group-hover:translate-x-1">→</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan --}}
            <div class="space-y-5">

                {{-- Menunggu persetujuan --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.45s">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Menunggu Persetujuan Laporan</h2>
                        <a href="{{ route('kepala-bps.approval.index') }}"
                            class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($laporanMenunggu->isEmpty())
                        <div class="text-center py-8 px-4">
                            <span
                                class="inline-flex w-10 h-10 rounded-full bg-green-50 text-green-600 items-center justify-center">✓</span>
                            <p class="text-sm text-gray-400 mt-2">Tidak ada laporan menunggu.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($laporanMenunggu as $i => $laporan)
                                <a href="{{ route('kepala-bps.approval.show', $laporan) }}"
                                    class="fx group flex items-center gap-2.5 px-5 py-3 hover:bg-amber-50/40 transition-colors"
                                    style="--d:{{ 0.5 + $i * 0.05 }}s">
                                    <span
                                        class="w-8 h-8 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $laporan->user->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $laporan->user->bagian->nama_bagian ?? '-' }} &middot;
                                            {{ $laporan->tanggal->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <span
                                        class="text-gray-300 transition group-hover:translate-x-1 group-hover:text-[#1F3864]">→</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Pekerjaan yang saya berikan --}}
                <div class="fx bg-white border border-gray-200 rounded-2xl overflow-hidden" style="--d:.55s">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Pekerjaan yang Saya Berikan</h2>
                        <a href="{{ route('pekerjaan.index') }}"
                            class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($tugasSayaBerikan->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Belum ada pekerjaan yang diberikan.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasSayaBerikan as $i => $tugas)
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
                                <a href="{{ route('pekerjaan.show', $tugas) }}"
                                    class="fx group block px-5 py-3 hover:bg-gray-50/70 transition-colors"
                                    style="--d:{{ 0.6 + $i * 0.05 }}s">
                                    <div class="flex items-center justify-between gap-2">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate group-hover:text-[#1F3864]">
                                            {{ $tugas->judul }}</p>
                                        <span
                                            class="inline-flex items-center gap-1 {{ $badge[0] }} text-[10px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset shrink-0">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>{{ $badge[2] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        {{ $tugas->penerimaTugas?->name ?? '-' }} &middot;
                                        {{ $tugas->bagian->nama_bagian ?? '-' }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
