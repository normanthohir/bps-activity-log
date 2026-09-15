{{-- resources/views/tugas/index-kepala-bps.blade.php --}}
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

        @keyframes fade-in-row {
            from {
                opacity: 0;
                transform: translateX(-6px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-row {
            animation: fade-in-row 0.4s ease-out both;
        }
    </style>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="animate-in flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Tugas yang Diberikan</h1>
                <p class="text-sm text-gray-500 mt-0.5">Semua tugas yang Anda berikan ke staf di seluruh bagian.</p>
            </div>
            <x-action-button href="{{ route('kepala-bps.tugas.create') }}">
                Beri Tugas
            </x-action-button>

        </div>

        {{-- Filter card --}}
        <form method="GET" action="{{ route('kepala-bps.tugas.index') }}"
            class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-4 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div class="lg:col-span-2">
                    <label class="text-xs text-gray-500 block mb-1">Cari judul / nama staf</label>
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" name="cari" value="{{ request('cari') }}"
                            placeholder="cth: rekap triwulan..."
                            class="w-full text-sm rounded-lg border-gray-300 pl-9 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-500 block mb-1">Bagian</label>
                    <select name="bagian"
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">Semua bagian</option>
                        @foreach ($daftarBagian as $b)
                            <option value="{{ $b->id }}" @selected(request('bagian') == $b->id)>{{ $b->nama_bagian }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500 block mb-1">Status Tugas</label>
                    <select name="status"
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">Semua status</option>
                        <option value="belum_dikerjakan" @selected(request('status') === 'belum_dikerjakan')>Belum Dikerjakan</option>
                        <option value="dikerjakan" @selected(request('status') === 'dikerjakan')>Dikerjakan</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                    Terapkan
                </button>
                @if (request()->hasAny(['cari', 'bagian', 'status']))
                    <a href="{{ route('kepala-bps.tugas.index') }}"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Table card --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 overflow-visible">
            @if ($tugas->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">
                        {{ request()->hasAny(['cari', 'bagian', 'status']) ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ request()->hasAny(['cari', 'bagian', 'status']) ? 'Coba ubah kata kunci atau filter.' : 'Buat tugas baru untuk diberikan ke staf.' }}
                    </p>
                </div>
            @else
                <!-- PERBAIKAN 2: Menggunakan 'overflow-x-auto overflow-y-visible' agar tabel tetap bisa di-scroll horizontal pada hp, tapi tidak memotong dropdown ke bawah -->
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Judul</th>
                                <th class="py-3 px-5 font-medium">Ditugaskan Ke</th>
                                <th class="py-3 px-5 font-medium">Bagian</th>
                                <th class="py-3 px-5 font-medium">Status Laporan</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tugas as $i => $item)
                                @php
                                    $laporanTerkini = $item->laporanHarian->first();
                                @endphp
                                <!-- PERBAIKAN 3: Menambahkan class 'relative' dan membalik susunan tumpukan z-index menggunakan hitungan matematika loop (z-index: {{ 100 - $i }}) -->
                                <tr class="animate-row hover:bg-gray-50/70 transition-colors relative"
                                    style="animation-delay: {{ min($i, 10) * 0.04 }}s; z-index: {{ 100 - $i }};">
                                    <td class="py-3.5 px-5">
                                        <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                        @if ($item->deskripsi)
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ Str::limit($item->deskripsi, 50) }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-[10px] font-semibold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($item->penerimaTugas->name ?? '-', 0, 1)) }}
                                            </span>
                                            <span class="text-gray-700">{{ $item->penerimaTugas->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-500 text-xs">
                                        {{ $item->bagian->nama_bagian ?? '—' }}</td>
                                    <td class="py-3.5 px-5">
                                        @if (!$laporanTerkini || in_array($laporanTerkini->status, ['draft']))
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Belum dikerjakan
                                            </span>
                                        @elseif ($laporanTerkini->status === 'menunggu')
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu persetujuan
                                            </span>
                                        @elseif ($laporanTerkini->status === 'disetujui')
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                Selesai
                                            </span>
                                        @elseif ($laporanTerkini->status === 'dikembalikan')
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                Dikembalikan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <!-- PERBAIKAN 4: Menambahkan kelas 'relative z-50' pada wrapper dropdown -->
                                        <div class="flex items-center justify-end gap-2 relative z-50">
                                            <x-dropdown-action>
                                                <a href="{{ route('kepala-bps.tugas.show', $item) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Lihat Detail
                                                </a>

                                                @if ($item->status === 'belum_dikerjakan')
                                                    <a href="{{ route('kepala-bps.tugas.edit', $item) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Edit Tugas
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('kepala-bps.tugas.destroy', $item) }}"
                                                        onsubmit="return confirm('Hapus tugas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </x-dropdown-action>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
