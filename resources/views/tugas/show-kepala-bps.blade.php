{{-- resources/views/tugas/show-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6 mb-6">
            <p class="font-medium text-lg mb-1">{{ $tugas->judul }}</p>
            <p class="text-sm text-gray-500 mb-4">
                Ditugaskan ke {{ $tugas->penerimaTugas->name }} — {{ $tugas->bagian->nama_bagian }}
            </p>

            <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
            <p class="text-sm mb-4">{{ $tugas->deskripsi ?? '-' }}</p>

            <p class="text-sm text-gray-500 mb-1">Tenggat</p>
            <p class="text-sm">{{ $tugas->tenggat?->format('d M Y') ?? '-' }}</p>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium mb-3">Riwayat laporan terkait tugas ini</p>

            @forelse ($tugas->laporanHarian as $laporan)
                <div class="border-t py-3">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-sm">{{ $laporan->tanggal->format('d M Y') }}</p>
                        <x-status-badge :status="$laporan->status" />
                    </div>
                    <p class="text-sm text-gray-600">{{ $laporan->uraian }}</p>

                    @if ($laporan->status === 'menunggu')
                        <div class="flex gap-2 mt-2">
                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}">
                                @csrf
                                <input type="hidden" name="aksi" value="disetujui">
                                <button class="text-xs px-2 py-1 rounded-lg border">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}">
                                @csrf
                                <input type="hidden" name="aksi" value="ditolak">
                                <button class="text-xs px-2 py-1 rounded-lg border text-red-600">Tolak</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4">Staf belum melaporkan progres tugas ini.</p>
            @endforelse
        </div>

        <a href="{{ route('kepala-bps.tugas.index') }}" class="text-sm underline mt-4 inline-block">← Kembali</a>

    </div>
</x-app-layout>