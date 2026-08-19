{{-- resources/views/dashboard/kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-5 mb-6 flex justify-between items-center">
            <div>
                <p class="font-medium text-lg">Kepala BPS Kota Ambon</p>
                <p class="text-sm text-gray-500">Akses seluruh bagian</p>
            </div>
            <a href="{{ route('kepala-bps.tugas.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Beri tugas
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Total laporan bulan ini</p>
                <p class="text-2xl font-medium">{{ $totalLaporanBulanIni }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Menunggu approval (semua bagian)</p>
                <p class="text-2xl font-medium">{{ $menungguApproval }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <p class="font-medium mb-3">Rekap per bagian</p>
            <table class="w-full text-sm">
                @foreach ($rekapPerBagian as $bagian)
                    <tr class="border-t">
                        <td class="py-2">{{ $bagian->nama_bagian }}</td>
                        <td class="py-2 text-right text-gray-500">{{ $bagian->pegawai_count }} pegawai</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('kepala-bps.approval.index', ['bagian' => $bagian->id]) }}"
                               class="text-sm underline">Lihat</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

    </div>
</x-app-layout>
