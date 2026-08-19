{{-- resources/views/rekap/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Rekap Kantor</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ringkasan laporan seluruh bagian bulan {{ now()->translatedFormat('F Y') }}.</p>
        </div>

        {{-- Summary stat cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
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
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
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
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Laporan Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $rekapPerBagian->sum('total_laporan') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rekap table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Rekap Per Bagian</h2>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                        <th class="py-3 px-5 font-medium">Bagian</th>
                        <th class="py-3 px-5 font-medium text-center">Pegawai</th>
                        <th class="py-3 px-5 font-medium text-center">Laporan Bulan Ini</th>
                        <th class="py-3 px-5 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rekapPerBagian as $item)
                        <tr class="hover:bg-gray-50/70 transition-colors">
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
                            <td class="py-3.5 px-5 text-center">
                                @if ($item->total_laporan > 0)
                                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-[#1F3864]">
                                        {{ $item->total_laporan }}
                                        <span class="text-xs font-normal text-gray-500">laporan</span>
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">0</span>
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
                            <td colspan="4" class="py-12 text-center">
                                <p class="text-sm text-gray-500">Belum ada data bagian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
