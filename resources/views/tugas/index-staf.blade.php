{{-- resources/views/tugas/index-staf.blade.php --}}
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
        <div class="animate-in mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Tugas Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar tugas yang ditugaskan kepada Anda.</p>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('staf.tugas.index') }}"
            class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-4 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <div class="sm:col-span-2">
                    <label class="text-xs text-gray-500 block mb-1">Cari judul tugas</label>
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" name="cari" value="{{ request('cari') }}"
                            placeholder="cth: rekap bulanan..."
                            class="w-full text-sm rounded-lg border-gray-300 pl-9 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Status</label>
                    <select name="status"
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">Semua status aktif</option>
                        <option value="belum_dikerjakan" @selected(request('status') === 'belum_dikerjakan')>Belum Dikerjakan</option>
                        <option value="dikerjakan" @selected(request('status') === 'dikerjakan')>Dikerjakan</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                    Terapkan
                </button>
                @if (request()->hasAny(['cari', 'status']))
                    <a href="{{ route('staf.tugas.index') }}"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Tugas Aktif --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Tugas Aktif</h2>
            </div>

            @if ($tugasAktif->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">
                        {{ request()->hasAny(['cari', 'status']) ? 'Tidak ada tugas yang cocok' : 'Tidak ada tugas aktif' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ request()->hasAny(['cari', 'status']) ? 'Coba ubah kata kunci atau filter.' : 'Saat ini Anda belum memiliki tugas.' }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Judul</th>
                                <th class="py-3 px-5 font-medium">Diberikan Oleh</th>
                                <th class="py-3 px-5 font-medium">Tenggat</th>
                                <th class="py-3 px-5 font-medium">Status</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tugasAktif as $i => $tugas)
                                @php
                                    $badge =
                                        $tugas->status === 'dikerjakan'
                                            ? ['bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500']
                                            : ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400'];
                                @endphp
                                <tr class="animate-row hover:bg-gray-50/70 transition-colors relative"
                                    style="animation-delay: {{ min($i, 10) * 0.04 }}s; z-index: {{ 100 - $i }};">

                                    <td class="py-3.5 px-5">
                                        <p class="font-medium text-gray-900">{{ $tugas->judul }}</p>
                                        @if ($tugas->deskripsi)
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ Str::limit($tugas->deskripsi, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-[10px] font-semibold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($tugas->pemberiTugas->name ?? '-', 0, 1)) }}
                                            </span>
                                            <span class="text-gray-700">{{ $tugas->pemberiTugas->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">
                                        {{ $tugas->tenggat ? $tugas->tenggat->format('d M Y') : '—' }}
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <span
                                            class="inline-flex items-center gap-1.5 {{ $badge[0] }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                            {{ ucwords(str_replace('_', ' ', $tugas->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center justify-end">
                                            <x-dropdown-action>
                                                @if (auth()->user()->isKepalaBagian())
                                                    <a href="{{ route('kabag.tugas-aktif.show', $tugas) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Lihat Detail
                                                    </a>
                                                @elseif (auth()->user()->isStaf())
                                                    <a href="{{ route('staf.tugas.show', $tugas) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Lihat Detail
                                                    </a>
                                                @endif
                                                @if ($tugas->status !== 'dikerjakan')
                                                    @if (auth()->user()->isKepalaBagian())
                                                        <a href="{{ route('kabag.laporan.create', ['tugas' => $tugas->id]) }}"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            Buat Laporan
                                                        </a>
                                                    @else(auth()->user()->isStaf())
                                                        <a href="{{ route('laporan.create', ['tugas' => $tugas->id]) }}"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            Buat Laporan
                                                        </a>
                                                    @endif
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

        {{-- Tugas Selesai --}}
        @if ($tugasSelesai->count())
            <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Tugas Selesai</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Judul</th>
                                <th class="py-3 px-5 font-medium">Diberikan Oleh</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tugasSelesai as $i => $tugas)
                                <tr class="animate-row hover:bg-gray-50/70 transition-colors"
                                    style="animation-delay: {{ min($i, 10) * 0.04 }}s">
                                    <td class="py-3 px-5">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-green-500 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span class="text-gray-700">{{ $tugas->judul }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-5 text-gray-500 text-xs">
                                        {{ $tugas->pemberiTugas->name ?? '—' }}
                                    </td>
                                    <td class="py-3 px-5 text-right">
                                        <a href="{{ route('staf.tugas.show', $tugas) }}"
                                            class="text-[#1F3864] hover:underline text-sm font-medium">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $tugasSelesai->links() }}
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
