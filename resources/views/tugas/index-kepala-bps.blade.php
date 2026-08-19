{{-- resources/views/tugas/index-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <p class="font-medium text-lg">Tugas yang Diberikan</p>
            <a href="{{ route('kepala-bps.tugas.create') }}" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Beri tugas
            </a>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Judul</th>
                        <th class="pb-2">Ditugaskan ke</th>
                        <th class="pb-2">Bagian</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tugas as $item)
                        @php
                            // Laporan terkait tugas ini yang paling baru (kalau ada)
                            $laporanTerkini = $item->laporanHarian->first();
                        @endphp
                        <tr class="border-b align-top">
                            <td class="py-3">{{ $item->judul }}</td>
                            <td class="py-3">{{ $item->penerimaTugas->name }}</td>
                            <td class="py-3">{{ $item->bagian->nama_bagian }}</td>
                            <td class="py-3">
                                @if (!$laporanTerkini || in_array($laporanTerkini->status, ['draft']))
                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">Belum dikerjakan</span>
                                @elseif ($laporanTerkini->status === 'menunggu')
                                    <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full">Menunggu persetujuan</span>
                                @elseif ($laporanTerkini->status === 'disetujui')
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Selesai (disetujui)</span>
                                @elseif ($laporanTerkini->status === 'dikembalikan')
                                    <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full">Dikembalikan ke staf</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    <div class="flex gap-2">
                                        @if ($laporanTerkini && $laporanTerkini->status === 'menunggu')
                                        <a href="{{ route('kepala-bps.tugas.edit', $item) }}" class="underline">Edit</a>
                                        <form method="POST" action="{{ route('kepala-bps.tugas.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus tugas ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 underline">Hapus</button>
                                        </form>
                                        @endif
                                        
                                        <a href="{{ route('kepala-bps.tugas.show', $item) }}" class="underline">Detail</a>
                                    </div>

                                    @if ($laporanTerkini && $laporanTerkini->status === 'menunggu')
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporanTerkini) }}">
                                                @csrf
                                                <input type="hidden" name="aksi" value="disetujui">
                                                <button class="text-xs px-2 py-1 rounded-lg border">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporanTerkini) }}">
                                                @csrf
                                                <input type="hidden" name="aksi" value="ditolak">
                                                <button class="text-xs px-2 py-1 rounded-lg border text-red-600">Tolak</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada tugas yang diberikan.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $tugas->links() }}
            </div>
        </div>

    </div>
</x-app-layout>