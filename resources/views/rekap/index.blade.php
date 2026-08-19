{{-- resources/views/rekap/index.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        <p class="font-medium text-lg mb-4">Rekap Seluruh Kantor</p>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Bagian</th>
                        <th class="pb-2">Jumlah Pegawai</th>
                        <th class="pb-2">Laporan Bulan Ini</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekapPerBagian as $bagian)
                        <tr class="border-b">
                            <td class="py-3">{{ $bagian->nama_bagian }}</td>
                            <td class="py-3">{{ $bagian->pegawai_count }}</td>
                            <td class="py-3">{{ $bagian->total_laporan }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('kepala-bps.approval.index', ['bagian' => $bagian->id]) }}"
                                   class="underline text-sm">Lihat laporan</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
