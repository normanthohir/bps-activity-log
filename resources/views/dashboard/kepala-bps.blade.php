{{-- resources/views/dashboard/kepala-bps.blade.php --}}
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
        .delay-4 { animation-delay: .2s; }
        .delay-5 { animation-delay: .25s; }
        .delay-6 { animation-delay: .3s; }

        @keyframes grow-bar { from { width: 0%; } }
        .animate-bar { animation: grow-bar 0.8s ease-out .3s both; }

        @keyframes fade-in-row {
            from { opacity: 0; transform: translateX(-6px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-row { animation: fade-in-row 0.4s ease-out both; }
    </style>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Greeting card --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#1F3864] to-[#2A4A82] rounded-2xl p-6 sm:p-8 mb-6 text-white animate-in">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide mb-1">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold">Kepala BPS Kota Ambon</h1>
                    <p class="text-white/70 text-sm mt-1">Akses seluruh bagian &middot; Rekap dan persetujuan lintas seksi</p>
                </div>
                <a href="{{ route('kepala-bps.tugas.create') }}"
                   class="relative inline-flex items-center gap-1.5 bg-white text-[#1F3864] text-sm font-semibold px-4 py-2.5 rounded-lg transition-all hover:bg-white/90 hover:scale-[1.03] active:scale-[0.98] shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Beri Tugas
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="animate-in delay-1 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Laporan Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalLaporanBulanIni }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu Approval</p>
                        <p class="text-2xl font-semibold {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $menungguApproval }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18M13.5 3v18m3.75-18v18m2.25-18v18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Bagian</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalBagian }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-4 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pegawai</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalPegawai }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom kiri: progress + rekap bagian --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Progress status laporan bulan ini --}}
                @php
                    $sisaMenunggu = max(0, $menungguApproval);
                    $totalDilihat = $disetujuiBulanIni + $dikembalikanBulanIni + $sisaMenunggu;
                    $pctDisetujui = $totalDilihat > 0 ? round($disetujuiBulanIni / $totalDilihat * 100) : 0;
                    $pctDikembalikan = $totalDilihat > 0 ? round($dikembalikanBulanIni / $totalDilihat * 100) : 0;
                    $pctMenunggu = $totalDilihat > 0 ? max(0, 100 - $pctDisetujui - $pctDikembalikan) : 0;
                @endphp
                <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900">Status Laporan Bulan Ini</h2>
                        <span class="text-xs text-gray-500">{{ $totalLaporanBulanIni }} total laporan</span>
                    </div>

                    @if ($totalDilihat > 0)
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden flex">
                            <div class="h-full bg-green-500 animate-bar" style="width: {{ $pctDisetujui }}%"></div>
                            <div class="h-full bg-amber-400 animate-bar" style="width: {{ $pctMenunggu }}%"></div>
                            <div class="h-full bg-red-400 animate-bar" style="width: {{ $pctDikembalikan }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-3 text-xs text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> Disetujui ({{ $disetujuiBulanIni }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Menunggu ({{ $sisaMenunggu }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> Dikembalikan ({{ $dikembalikanBulanIni }})</span>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-2">Belum ada laporan bulan ini.</p>
                    @endif
                </div>

                {{-- Rekap per bagian --}}
                <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <h2 class="font-semibold text-gray-900">Rekap Per Bagian</h2>
                        <a href="{{ route('kepala-bps.rekap') }}" class="text-sm text-[#1F3864] hover:underline font-medium">Lihat Detail</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                                    <th class="py-2.5 px-5 font-medium">Bagian</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Pegawai</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Laporan Bulan Ini</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Menunggu</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($rekapPerBagian as $i => $bagian)
                                    <tr class="animate-row hover:bg-gray-50/70 transition-colors" style="animation-delay: {{ $i * 0.05 }}s">
                                        <td class="py-3 px-5 font-medium text-gray-900">{{ $bagian->nama_bagian }}</td>
                                        <td class="py-3 px-5 text-right text-gray-500">{{ $bagian->pegawai_count }}</td>
                                        <td class="py-3 px-5 text-right text-gray-500">{{ $bagian->total_laporan_bulan_ini }}</td>
                                        <td class="py-3 px-5 text-right">
                                            @if ($bagian->menunggu_approval > 0)
                                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-2 py-0.5 rounded-full ring-1 ring-inset ring-amber-600/20">
                                                    {{ $bagian->menunggu_approval }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            <a href="{{ route('kepala-bps.approval.index', ['bagian' => $bagian->id]) }}"
                                               class="text-[#1F3864] hover:underline text-sm font-medium">
                                                Lihat Laporan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: laporan menunggu & tugas yang saya berikan --}}
            <div class="space-y-6">

                <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Menunggu Persetujuan</h2>
                        <a href="{{ route('kepala-bps.approval.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($laporanMenunggu->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Tidak ada laporan menunggu.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($laporanMenunggu as $laporan)
                                <a href="{{ route('kepala-bps.approval.show', $laporan) }}"
                                   class="flex items-center gap-2.5 px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <span class="w-7 h-7 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $laporan->user->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $laporan->user->bagian->nama_bagian ?? '-' }} &middot; {{ $laporan->tanggal->format('d M Y') }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="animate-in delay-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Tugas yang Saya Berikan</h2>
                        <a href="{{ route('kepala-bps.tugas.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($tugasSayaBerikan->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Belum ada tugas yang diberikan.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasSayaBerikan as $tugas)
                                @php
                                    $badge = match ($tugas->status) {
                                        'selesai' => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500', 'Selesai'],
                                        'dikerjakan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500', 'Dikerjakan'],
                                        default => ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400', 'Belum'],
                                    };
                                @endphp
                                <a href="{{ route('kepala-bps.tugas.show', $tugas) }}" class="block px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $tugas->judul }}</p>
                                        <span class="inline-flex items-center gap-1 {{ $badge[0] }} text-[10px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                            {{ $badge[2] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        {{ $tugas->penerimaTugas->name }} &middot; {{ $tugas->bagian->nama_bagian ?? '-' }}
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