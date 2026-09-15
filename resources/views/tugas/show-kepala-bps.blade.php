{{-- resources/views/tugas/show-kepala-bps.blade.php --}}
<x-app-layout>
    <style>
        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fade-slide-up 0.5s ease-out both; }
        .delay-1 { animation-delay: .05s; }
        .delay-2 { animation-delay: .1s; }
    </style>

    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <a href="{{ route('kepala-bps.tugas.index') }}"
            class="animate-in mb-4 inline-flex items-center gap-1.5 bg-[#1F3864]/10 border border-[#1F3864]/30 text-[#1F3864] hover:bg-[#1F3864]/20 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

        {{-- Header --}}
        <div class="animate-in mb-6 mt-4">
            <h1 class="text-xl font-semibold text-gray-900">{{ $tugas->judul }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Ditugaskan ke {{ $tugas->penerimaTugas?->name ?? '-' }} &middot;
                {{ $tugas->bagian?->nama_bagian ?? '-' }}
            </p>
        </div>

        {{-- Detail card --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tenggat</p>
                        <p class="text-sm font-medium text-gray-900">{{ $tugas->tenggat?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
                        <p class="text-sm text-gray-900">{{ $tugas->deskripsi ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat laporan --}}
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Riwayat Laporan Terkait</h2>
            </div>

            @if ($tugas->laporanHarian->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Staf belum melaporkan progres tugas ini.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($tugas->laporanHarian as $laporan)
                        <div class="px-5 py-4">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-sm font-medium text-gray-900">{{ $laporan->tanggal->format('d M Y') }}</p>
                                <x-status-badge :status="$laporan->status" />
                            </div>
                            <p class="text-sm text-gray-600">{{ $laporan->uraian }}</p>

                            @if ($laporan->status === 'menunggu')
                                <div class="pt-4 mt-3 border-t border-gray-100"
                                    x-data="{ tolak: {{ $errors->has('catatan') ? 'true' : 'false' }} }">

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
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>