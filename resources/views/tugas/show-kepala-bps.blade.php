{{-- resources/views/tugas/show-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">{{ $tugas->judul }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Ditugaskan ke {{ $tugas->penerimaTugas->name }} &middot; {{ $tugas->bagian->nama_bagian }}
            </p>
        </div>

        {{-- Detail card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
                        <p class="text-sm text-gray-900">{{ $tugas->deskripsi ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tenggat</p>
                        <p class="text-sm font-medium text-gray-900">{{ $tugas->tenggat?->format('d M Y') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat laporan --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Riwayat Laporan Terkait</h2>
            </div>

            @if ($tugas->laporanHarian->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
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
                                <div class="flex gap-2 mt-2">
                                    <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}">
                                        @csrf
                                        <input type="hidden" name="aksi" value="disetujui">
                                        <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporan) }}">
                                        @csrf
                                        <input type="hidden" name="aksi" value="ditolak">
                                        <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <a href="{{ route('kepala-bps.tugas.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mt-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

    </div>
</x-app-layout>
