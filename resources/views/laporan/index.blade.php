{{-- resources/views/laporan/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Daftar Laporan Harian</h1>
                <p class="text-sm text-gray-500 mt-0.5">Riwayat laporan aktivitas yang telah Anda buat.</p>
            </div>
            <a href="{{ route('laporan.create') }}"
               class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Laporan Baru
            </a>
        </div>

        {{-- Filter by status --}}
        <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
            <select name="status" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
            </select>
            @if (request('status'))
                <a href="{{ route('laporan.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($laporan->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    @if (request('status'))
                        <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada laporan dengan status ini</p>
                        <p class="text-sm text-gray-500 mt-1">Coba ubah filter status yang dipilih.</p>
                        <a href="{{ route('laporan.index') }}" class="inline-block mt-4 text-sm text-[#1F3864] font-medium hover:underline">
                            Reset filter
                        </a>
                    @else
                        <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan</p>
                        <p class="text-sm text-gray-500 mt-1">Mulai buat laporan aktivitas harian pertama Anda.</p>
                        <a href="{{ route('laporan.create') }}"
                           class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            + Buat Laporan
                        </a>
                    @endif
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Tanggal</th>
                            <th class="py-3 px-5 font-medium">Uraian</th>
                            <th class="py-3 px-5 font-medium">Lokasi</th>
                            <th class="py-3 px-5 font-medium">Status</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($laporan as $item)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">{{ $item->tanggal->format('d M Y') }}</td>
                                <td class="py-3.5 px-5 text-gray-900">{{ Str::limit($item->uraian, 50) }}</td>
                                <td class="py-3.5 px-5">
                                    @php
                                        $lokasiStyle = match($item->lokasi) {
                                            'kantor' => 'bg-blue-50 text-blue-700',
                                            'lapangan' => 'bg-green-50 text-green-700',
                                            'dinas_luar' => 'bg-purple-50 text-purple-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $lokasiStyle }}">
                                        {{ ucwords(str_replace('_', ' ', $item->lokasi)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    <x-status-badge :status="$item->status" />
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('laporan.show', $item) }}"
                                           class="text-[#1F3864] hover:underline text-sm font-medium transition-colors">
                                            Lihat
                                        </a>
                                        @if ($item->status === 'draft' || $item->status === 'dikembalikan')
                                            <a href="{{ route('laporan.edit', $item) }}"
                                               class="text-gray-600 hover:text-[#1F3864] text-sm font-medium transition-colors">
                                                Edit
                                            </a>
                                        @endif
                                        @if ($item->status === 'draft')
                                            <form method="POST" action="{{ route('laporan.update', $item) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tanggal" value="{{ $item->tanggal->toDateString() }}">
                                                <input type="hidden" name="uraian" value="{{ $item->uraian }}">
                                                <input type="hidden" name="lokasi" value="{{ $item->lokasi }}">
                                                <input type="hidden" name="tugas_id" value="{{ $item->tugas_id }}">
                                                <input type="hidden" name="jam_mulai" value="{{ $item->jam_mulai }}">
                                                <input type="hidden" name="jam_selesai" value="{{ $item->jam_selesai }}">
                                                <input type="hidden" name="output" value="{{ $item->output }}">
                                                <input type="hidden" name="file_lampiran" value="{{ $item->file_lampiran }}">
                                                <input type="hidden" name="aksi" value="ajukan">
                                                <button type="submit"
                                                        class="text-green-600 hover:text-green-700 text-sm font-medium transition-colors">
                                                    Kirim
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('laporan.destroy', $item) }}" class="inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @if ($item->status === 'dikembalikan' && $item->logApproval->last())
                                <tr class="bg-red-50">
                                    <td colspan="5" class="px-5 py-2 text-xs text-red-700">
                                        Catatan penolakan: {{ $item->logApproval->last()->catatan ?? '-' }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $laporan->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
