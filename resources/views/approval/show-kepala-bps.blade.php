{{-- resources/views/approval/show-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        
        <a href="{{ route('kepala-bps.approval.index') }}"
            class="mb-2 inline-flex items-center gap-1.5 bg-[#1F3864]/10 border border-[#1F3864]/30 text-[#1F3864] hover:bg-[#1F3864]/20 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

        <div class="bg-white rounded-xl border p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Tengat</p>
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

            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Output/hasil</p>
                <p class="text-sm">{{ $laporan->output ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                    <p class="text-sm capitalize">{{ str_replace('_', ' ', $laporan->lokasi) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Lampiran</p>
                    @if ($laporan->file_lampiran)
                        <a href="{{ $laporan->file_lampiran }}" target="_blank" class="text-sm underline">
                            Lihat file lampiran
                        </a>
                    @else
                        <p class="text-sm text-gray-400">Tidak ada</p>
                    @endif
                </div>
            </div>

            @if ($laporan->status === 'menunggu')
                <div class="pt-4 border-t">

                    {{-- Tombol Setujui: langsung submit, tidak perlu catatan --}}
                    <div id="area-tombol-utama" class="flex gap-2">
                        <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}">
                            @csrf
                            <input type="hidden" name="aksi" value="disetujui">
                            <button
                                class="bg-green-50 hover:bg-green-100 text-green-600 border border-green-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                Setujui
                            </button>
                        </form>

                        <button type="button" onclick="tampilkanFormTolak()"
                            class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Tolak
                        </button>
                    </div>

                    {{-- Form penolakan: muncul setelah tombol "Tolak" diklik, wajib isi catatan --}}
                    <form id="form-tolak" method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                        class="hidden mt-3">
                        @csrf
                        <input type="hidden" name="aksi" value="ditolak">

                        <label class="text-sm text-gray-500 block mb-1">Alasan penolakan (wajib diisi)</label>
                        <textarea name="catatan" rows="3" required
                            class="w-full rounded-lg border-gray-300 text-sm @error('catatan') border-red-500 @enderror"
                            placeholder="Jelaskan apa yang perlu diperbaiki staf...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2 mt-2">
                            <button type="submit"
                                class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                Kirim penolakan
                            </button>
                            <button type="button" onclick="batalkanFormTolak()"
                                class="bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <script>
                    function tampilkanFormTolak() {
                        document.getElementById('area-tombol-utama').classList.add('hidden');
                        document.getElementById('form-tolak').classList.remove('hidden');
                    }

                    function batalkanFormTolak() {
                        document.getElementById('form-tolak').classList.add('hidden');
                        document.getElementById('area-tombol-utama').classList.remove('hidden');
                    }

                    // Kalau validasi gagal (misal catatan kosong) dan halaman reload,
                    // otomatis tampilkan lagi form tolaknya supaya pesan error terlihat
                    @if ($errors->has('catatan'))
                        tampilkanFormTolak();
                    @endif
                </script>
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

    </div>
</x-app-layout>
