{{-- resources/views/rekap/index.blade.php --}}
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

        @keyframes grow-bar { from { width: 0%; } }
        .animate-bar { animation: grow-bar 0.8s ease-out .2s both; }

        @keyframes fade-in-row {
            from { opacity: 0; transform: translateX(-6px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-row { animation: fade-in-row 0.4s ease-out both; }
    </style>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6 animate-in">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Rekap Kantor</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Ringkasan laporan seluruh bagian &middot; periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}.
                </p>
            </div>

            {{-- Tombol export --}}
            <div class="flex gap-2">
                <a href="{{ route('kepala-bps.rekap.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:border-red-300 hover:bg-red-50 text-gray-700 hover:text-red-600 text-sm font-medium px-3.5 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    PDF
                </a>
                <a href="{{ route('kepala-bps.rekap.export-excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:border-green-300 hover:bg-green-50 text-gray-700 hover:text-green-600 text-sm font-medium px-3.5 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                    Excel
                </a>
            </div>
        </div>

        {{-- Filter periode --}}
        <form method="GET" action="{{ route('kepala-bps.rekap') }}"
            class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Bulan</label>
                <select name="bulan" class="text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    @foreach (['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $angka => $nama)
                        <option value="{{ $angka }}" @selected($bulan == $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tahun</label>
                <select name="tahun" class="text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    @for ($t = now()->year; $t >= now()->year - 3; $t--)
                        <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit"
                class="bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Tampilkan
            </button>
        </form>

        {{-- Summary stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="animate-in delay-1 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Bagian</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $rekapPerBagian->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pegawai</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $rekapPerBagian->sum('pegawai_count') }}</p>
                    </div>
                </div>
            </div>
            <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Laporan Periode Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $rekapPerBagian->sum('total_laporan') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rekap table dengan kontribusi per bagian --}}
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Rekap Per Bagian</h2>
            </div>

            @php $totalKeseluruhan = $rekapPerBagian->sum('total_laporan'); @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Bagian</th>
                            <th class="py-3 px-5 font-medium text-center">Pegawai</th>
                            <th class="py-3 px-5 font-medium">Laporan</th>
                            <th class="py-3 px-5 font-medium text-center">Disetujui</th>
                            <th class="py-3 px-5 font-medium text-center">Menunggu</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rekapPerBagian as $i => $item)
                            @php
                                $pct = $totalKeseluruhan > 0 ? round($item->total_laporan / $totalKeseluruhan * 100) : 0;
                            @endphp
                            <tr class="animate-row hover:bg-gray-50/70 transition-colors" style="animation-delay: {{ $i * 0.05 }}s">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-[#DCE6F1] text-[#1F3864] text-xs font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->kode_bagian, 0, 2)) }}
                                        </span>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $item->nama_bagian }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->kode_bagian }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-center text-gray-700">{{ $item->pegawai_count }}</td>
                                <td class="py-3.5 px-5 w-48">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-[#1F3864] w-6 text-right">{{ $item->total_laporan }}</span>
                                        <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full bg-[#1F3864] animate-bar" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-9">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="text-green-600 font-medium">{{ $item->disetujui }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    @if ($item->menunggu > 0)
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-medium px-2 py-0.5 rounded-full ring-1 ring-inset ring-amber-600/20">
                                            {{ $item->menunggu }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="{{ route('kepala-bps.approval.index', ['bagian' => $item->id]) }}"
                                        class="text-[#1F3864] hover:underline text-sm font-medium">
                                        Lihat Laporan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <p class="text-sm text-gray-500">Belum ada data bagian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>