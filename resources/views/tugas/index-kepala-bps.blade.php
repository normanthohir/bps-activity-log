{{-- resources/views/tugas/index-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Tugas yang Diberikan</h1>
                <p class="text-sm text-gray-500 mt-0.5">Semua tugas yang Anda berikan ke staf di seluruh bagian.</p>
            </div>
            <a href="{{ route('kepala-bps.tugas.create') }}"
               class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Beri Tugas
            </a>
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($tugas->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada tugas</p>
                    <p class="text-sm text-gray-500 mt-1">Buat tugas baru untuk diberikan ke staf.</p>
                    <a href="{{ route('kepala-bps.tugas.create') }}"
                       class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        + Beri Tugas
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Judul</th>
                            <th class="py-3 px-5 font-medium">Ditugaskan Ke</th>
                            <th class="py-3 px-5 font-medium">Bagian</th>
                            <th class="py-3 px-5 font-medium">Status Laporan</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($tugas as $item)
                            @php
                                $laporanTerkini = $item->laporanHarian->first();
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5">
                                    <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                    @if ($item->deskripsi)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($item->deskripsi, 50) }}</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->penerimaTugas->name ?? '?', 0, 1)) }}
                                        </span>
                                        <span class="text-gray-700">{{ $item->penerimaTugas->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-gray-500 text-xs">{{ $item->bagian->nama_bagian ?? '—' }}</td>
                                <td class="py-3.5 px-5">
                                    @if (!$laporanTerkini || in_array($laporanTerkini->status, ['draft']))
                                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-gray-100 text-gray-600">Belum dikerjakan</span>
                                    @elseif ($laporanTerkini->status === 'menunggu')
                                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-amber-50 text-amber-700">Menunggu persetujuan</span>
                                    @elseif ($laporanTerkini->status === 'disetujui')
                                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-green-50 text-green-700">Selesai</span>
                                    @elseif ($laporanTerkini->status === 'dikembalikan')
                                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md bg-red-50 text-red-700">Dikembalikan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($laporanTerkini && $laporanTerkini->status === 'menunggu')
                                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporanTerkini) }}">
                                                @csrf
                                                <input type="hidden" name="aksi" value="disetujui">
                                                <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                    </svg>
                                                    Setujui
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('kepala-bps.approval.proses', $laporanTerkini) }}">
                                                @csrf
                                                <input type="hidden" name="aksi" value="ditolak">
                                                <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('kepala-bps.tugas.show', $item) }}"
                                           class="text-[#1F3864] hover:underline text-sm font-medium">
                                            Detail
                                        </a>
                                        @if ($laporanTerkini && $laporanTerkini->status === 'menunggu')
                                            <a href="{{ route('kepala-bps.tugas.edit', $item) }}"
                                               class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('kepala-bps.tugas.destroy', $item) }}"
                                                  onsubmit="return confirm('Hapus tugas ini?')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $tugas->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
