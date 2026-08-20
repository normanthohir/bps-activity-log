{{-- resources/views/tugas/show-staf.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">{{ $tugas->judul }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Diberikan oleh {{ $tugas->pemberiTugas?->name ?? '-' }}
                ({{ $tugas->pemberiTugas?->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                &middot; {{ $tugas->bagian?->nama_bagian ?? '-' }}
            </p>
        </div>

        {{-- Detail card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
                        <p class="text-sm text-gray-900">{{ $tugas->deskripsi ?? '-' }}</p>
                    </div>
                    <a href="{{ route('laporan.create', ['tugas' => $tugas->id]) }}"
                       class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Laporan
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tenggat</p>
                        <p class="text-sm font-medium text-gray-900">{{ $tugas->tenggat?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Status</p>
                        @php
                            $statusStyle = match($tugas->status) {
                                'selesai' => 'bg-green-50 text-green-700',
                                'sedang_dikerjakan' => 'bg-blue-50 text-blue-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                            $statusLabel = match($tugas->status) {
                                'selesai' => 'Selesai',
                                'sedang_dikerjakan' => 'Sedang Dikerjakan',
                                default => 'Belum Dikerjakan',
                            };
                        @endphp
                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $statusStyle }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat laporan --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Riwayat Laporan</h2>
            </div>

            @if ($tugas->laporanHarian->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan</p>
                    <p class="text-sm text-gray-500 mt-1">Mulai buat laporan untuk tugas ini.</p>
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

                            @if ($laporan->status === 'dikembalikan')
                                <div class="mt-2 bg-red-50 text-red-700 text-xs px-3 py-2 rounded-lg">
                                    Catatan: {{ $laporan->logApproval->last()->catatan ?? '-' }}
                                    — <a href="{{ route('laporan.edit', $laporan) }}" class="underline font-medium">Revisi laporan ini</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <a href="{{ route('staf.tugas.index') }}" class="inline-flex items-center gap-1.5 bg-[#1F3864]/10 border border-[#1F3864]/30 text-[#1F3864] hover:bg-[#1F3864]/20 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

    </div>
</x-app-layout>
