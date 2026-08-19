{{-- resources/views/tugas/show-staf.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-medium text-lg">{{ $tugas->judul }}</p>
                    <p class="text-sm text-gray-500">
                        Diberikan oleh {{ $tugas->pemberiTugas->name }}
                        ({{ $tugas->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                        · {{ $tugas->bagian->nama_bagian }}
                    </p>
                </div>
                <a href="{{ route('laporan.create', ['tugas' => $tugas->id]) }}"
                   class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                    + Buat laporan
                </a>
            </div>

            <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
            <p class="text-sm mb-4">{{ $tugas->deskripsi ?? '-' }}</p>

            <div class="flex gap-8">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Tenggat</p>
                    <p class="text-sm">{{ $tugas->tenggat?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Status</p>
                    <p class="text-sm">{{ ucwords(str_replace('_', ' ', $tugas->status)) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium mb-3">Riwayat laporan yang sudah dibuat untuk tugas ini</p>

            @forelse ($tugas->laporanHarian as $laporan)
                <div class="border-t py-3">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-sm">{{ $laporan->tanggal->format('d M Y') }}</p>
                        <x-status-badge :status="$laporan->status" />
                    </div>
                    <p class="text-sm text-gray-600">{{ $laporan->uraian }}</p>

                    @if ($laporan->status === 'dikembalikan')
                        <p class="text-xs text-red-600 mt-1">
                            Catatan: {{ $laporan->logApproval->last()->catatan ?? '-' }}
                            — <a href="{{ route('laporan.edit', $laporan) }}" class="underline">revisi laporan ini</a>
                        </p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4">Belum ada laporan untuk tugas ini.</p>
            @endforelse
        </div>

        <a href="{{ route('tugas.index') }}" class="text-sm underline mt-4 inline-block">← Kembali ke tugas aktif</a>

    </div>
</x-app-layout>
