{{-- resources/views/dashboard/kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Greeting card --}}
        <div class="bg-[#1F3864] rounded-2xl p-6 mb-6 text-white">
            <h1 class="text-xl font-semibold">Kepala BPS Kota Ambon</h1>
            <p class="text-white/70 text-sm mt-0.5">Akses seluruh bagian &middot; Rekap dan persetujuan lintas seksi</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Laporan Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalLaporanBulanIni }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu Approval (Semua Bagian)</p>
                        <p class="text-2xl font-semibold {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $menungguApproval }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rekap per bagian --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-semibold text-gray-900">Rekap Per Bagian</h2>
                <a href="{{ route('kepala-bps.rekap') }}" class="text-sm text-[#1F3864] hover:underline font-medium">Lihat Detail</a>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                        <th class="py-2.5 px-5 font-medium">Bagian</th>
                        <th class="py-2.5 px-5 font-medium text-right">Pegawai</th>
                        <th class="py-2.5 px-5 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($rekapPerBagian as $bagian)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-3 px-5 font-medium text-gray-900">{{ $bagian->nama_bagian }}</td>
                            <td class="py-3 px-5 text-right text-gray-500">{{ $bagian->pegawai_count }} pegawai</td>
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
</x-app-layout>
