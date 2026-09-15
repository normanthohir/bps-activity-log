{{-- resources/views/approval/show-kepala-bps.blade.php --}}
<x-app-layout>
    <style>
        @keyframes fade-slide-up {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fade-slide-up 0.5s ease-out both;
        }

        .delay-1 {
            animation-delay: .05s;
        }

        .delay-2 {
            animation-delay: .1s;
        }
    </style>

    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <a href="{{ route('kepala-bps.approval.index') }}"
            class="animate-in mb-4 inline-flex items-center gap-1.5 bg-[#1F3864]/10 border border-[#1F3864]/30 text-[#1F3864] hover:bg-[#1F3864]/20 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

        <div
            class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-6 mb-6 transition-all hover:shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <span
                        class="w-10 h-10 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="font-medium text-gray-900 text-lg leading-tight">{{ $laporan->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $laporan->user->bagian->nama_bagian ?? '-' }}</p>
                    </div>
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
                    <p class="text-sm text-gray-500 mb-1">Tanggal</p>
                    <p class="text-sm text-gray-900">{{ $laporan->tanggal->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Jam</p>
                    <p class="text-sm text-gray-900">
                        {{ $laporan->jam_mulai ?? '-' }} — {{ $laporan->jam_selesai ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Uraian kegiatan</p>
                <p class="text-sm text-gray-900">{{ $laporan->uraian }}</p>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-1">Output/hasil</p>
                <p class="text-sm text-gray-900">{{ $laporan->output ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                    <p class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $laporan->lokasi) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Lampiran</p>
                    @if ($laporan->file_lampiran)
                        <a href="{{ $laporan->file_lampiran }}" target="_blank"
                            class="text-sm text-[#1F3864] hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            Lihat file lampiran
                        </a>
                    @else
                        <p class="text-sm text-gray-400">Tidak ada</p>
                    @endif
                </div>
            </div>

            @if ($laporan->status === 'menunggu')
                <div class="pt-4 border-t border-gray-100" x-data="{ tolak: {{ $errors->has('catatan') ? 'true' : 'false' }} }">

                    {{-- Tombol utama --}}
                    <div x-show="!tolak" x-transition class="flex gap-2">
                        <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}"
                            x-data="{ loading: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="aksi" value="disetujui">
                            <x-loading-overlay message="Memproses persetujuan..." />
                            <button type="submit" :disabled="loading"
                                class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 disabled:opacity-70 disabled:cursor-not-allowed text-green-600 border border-green-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                <x-button-spinner x-show="loading" x-cloak />
                                <span x-text="loading ? 'Memproses...' : 'Setujui'"></span>
                            </button>
                        </form>

                        <button type="button" @click="tolak = true"
                            class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Tolak
                        </button>
                    </div>

                    {{-- Form penolakan --}}
                    <form x-show="tolak" x-transition method="POST"
                        action="{{ route('kepala-bps.approval.proses', $laporan) }}" class="mt-3"
                        x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="aksi" value="ditolak">

                        <label class="text-sm text-gray-500 block mb-1">Alasan penolakan (wajib diisi)</label>
                        <textarea name="catatan" rows="3" required
                            class="w-full rounded-lg text-sm focus:ring-red-500/20 @error('catatan') border-red-500 @else border-gray-300 @enderror"
                            placeholder="Jelaskan apa yang perlu diperbaiki staf...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        <x-loading-overlay message="Mengirim penolakan..." />

                        <div class="flex gap-2 mt-2">
                            <button type="submit" :disabled="loading"
                                class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 disabled:opacity-70 disabled:cursor-not-allowed text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                <x-button-spinner x-show="loading" x-cloak />
                                <span x-text="loading ? 'Mengirim...' : 'Kirim penolakan'"></span>
                            </button>
                            <button type="button" @click="tolak = false"
                                class="bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Riwayat approval --}}
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 p-6">
            <p class="font-medium text-gray-900 mb-3">Riwayat Persetujuan</p>

            @forelse ($laporan->logApproval as $log)
                <div class="border-t border-gray-100 first:border-t-0 py-3">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-900">
                            {{ $log->approver->name }}
                            <span class="text-gray-400 text-xs">
                                ({{ $log->approver->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                            </span>
                        </p>
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset
                            {{ $log->aksi === 'disetujui' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $log->aksi === 'disetujui' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ ucfirst($log->aksi) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('d M Y, H:i') }}</p>
                    @if ($log->catatan)
                        <p class="text-sm text-gray-600 mt-1">Catatan: {{ $log->catatan }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 py-2 text-center">Belum pernah diproses.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
