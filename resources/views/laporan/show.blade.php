{{-- resources/views/laporan/show.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Detail Laporan</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $laporan->tanggal->format('d M Y') }}</p>
        </div>

        {{-- Detail card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <x-status-badge :status="$laporan->status" />
                    @if ($laporan->status === 'draft')
                        <div class="flex gap-2">
                            <a href="{{ route('laporan.edit', $laporan) }}"
                               class="text-sm font-medium text-gray-600 hover:text-[#1F3864] transition-colors">
                                Edit
                            </a>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tanggal</p>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->tanggal->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Lokasi</p>
                        @php
                            $lokasiStyle = match($laporan->lokasi) {
                                'kantor' => 'bg-blue-50 text-blue-700',
                                'lapangan' => 'bg-green-50 text-green-700',
                                'dinas_luar' => 'bg-purple-50 text-purple-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $lokasiStyle }}">
                            {{ ucwords(str_replace('_', ' ', $laporan->lokasi)) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jam Mulai</p>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->jam_mulai ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jam Selesai</p>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->jam_selesai ?? '—' }}</p>
                    </div>
                </div>

                @if ($laporan->tugas)
                    <div class="mb-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Terkait Tugas</p>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->tugas->judul }}</p>
                    </div>
                @endif

                <div class="mb-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-1">Uraian Kegiatan</p>
                    <p class="text-sm text-gray-900">{{ $laporan->uraian }}</p>
                </div>

                @if ($laporan->output)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Output / Hasil</p>
                        <p class="text-sm text-gray-900">{{ $laporan->output }}</p>
                    </div>
                @endif

                @if ($laporan->file_lampiran)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Lampiran</p>
                        <a href="{{ $laporan->file_lampiran }}" target="_blank"
                           class="inline-flex items-center gap-1 text-sm text-[#1F3864] hover:underline font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.813a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.374" />
                            </svg>
                            Lihat Lampiran
                        </a>
                    </div>
                @endif

                @if ($laporan->disetujui_oleh)
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Disetujui Oleh</p>
                        <p class="text-sm font-medium text-gray-900">{{ $laporan->approver->name ?? '—' }}</p>
                        @if ($laporan->disetujui_pada)
                            <p class="text-xs text-gray-400">{{ $laporan->disetujui_pada->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat approval --}}
        @if ($laporan->logApproval->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Riwayat Approval</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($laporan->logApproval as $log)
                        <div class="px-5 py-4">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-sm font-medium text-gray-900">{{ $log->approver->name ?? '—' }}</p>
                                @if ($log->aksi === 'disetujui')
                                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-green-50 text-green-700">Disetujui</span>
                                @else
                                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-red-50 text-red-700">Dikembalikan</span>
                                @endif
                            </div>
                            @if ($log->catatan)
                                <p class="text-sm text-gray-600">{{ $log->catatan }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $log->created_at->format('d M Y H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('laporan.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>

    </div>
</x-app-layout>
