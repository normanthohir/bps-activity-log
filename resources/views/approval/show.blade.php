{{-- resources/views/approval/show-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-medium text-lg">{{ $laporan->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $laporan->user->bagian->nama_bagian ?? '-' }}</p>
                </div>
                <x-status-badge :status="$laporan->status" />
            </div>

            {{-- Asal laporan: terkait tugas atau laporan mandiri --}}
            @if ($laporan->tugas)
                <div class="bg-blue-50 rounded-lg p-3 mb-4 text-sm">
                    <p class="text-blue-700 font-medium mb-1">Terkait tugas</p>
                    <p class="text-blue-700">{{ $laporan->tugas->judul }}</p>
                    <p class="text-blue-600 text-xs mt-1">
                        Diberikan oleh {{ $laporan->tugas->pemberiTugas->name }}
                        ({{ $laporan->tugas->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                    </p>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-gray-600">
                    Laporan mandiri — tidak terkait tugas dari atasan.
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Tanggal</p>
                    <p class="text-sm">{{ $laporan->tanggal->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Jam</p>
                    <p class="text-sm">
                        {{ $laporan->jam_mulai ?? '-' }} — {{ $laporan->jam_selesai ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Uraian kegiatan</p>
                <p class="text-sm">{{ $laporan->uraian }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                    <p class="text-sm capitalize">{{ str_replace('_', ' ', $laporan->lokasi) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Output/hasil</p>
                    <p class="text-sm">{{ $laporan->output ?? '-' }}</p>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Lampiran</p>
                @if ($laporan->file_lampiran)
                    <a href="{{ Storage::url($laporan->file_lampiran) }}" target="_blank" class="text-sm underline">
                        Lihat file
                    </a>
                @else
                    <p class="text-sm text-gray-400">Tidak ada</p>
                @endif
            </div>

            @if ($laporan->status === 'menunggu')
                <div class="flex gap-2 pt-4 border-t">
                    <form method="POST" action="{{ route('kabag.approval.proses', $laporan) }}">
                        @csrf
                        <input type="hidden" name="aksi" value="disetujui">
                        <button class="text-sm px-4 py-2 rounded-lg border text-green-500 hover:text-green-50 border-green-300 hover:bg-green-500  duration-100">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('kabag.approval.proses', $laporan) }}">
                        @csrf
                        <input type="hidden" name="aksi" value="ditolak">
                        <button class="text-sm px-4 py-2 rounded-lg border border-red-300 hover:bg-red-500 text-red-600 hover:text-red-50 duration-100">Tolak</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Riwayat approval: siapa memproses, kapan, dan catatannya --}}
        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium mb-3">Riwayat persetujuan</p>

            @forelse ($laporan->logApproval as $log)
                <div class="border-t first:border-t-0 py-3">
                    <div class="flex justify-between items-center">
                        <p class="text-sm">
                            {{ $log->approver->name }}
                            <span class="text-gray-400 text-xs">
                                ({{ $log->approver->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                            </span>
                        </p>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full {{ $log->aksi === 'disetujui' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($log->aksi) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('d M Y, H:i') }}</p>
                    @if ($log->catatan)
                        <p class="text-sm text-gray-600 mt-1">Catatan: {{ $log->catatan }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2">Belum pernah diproses.</p>
            @endforelse
        </div>

        <a href="{{ route('kabag.approval.index') }}" class="text-sm underline mt-4 inline-block">← Kembali</a>

    </div>
</x-app-layout>
