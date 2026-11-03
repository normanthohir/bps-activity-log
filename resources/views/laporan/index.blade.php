{{-- resources/views/laporan/index.blade.php --}}
@php
    $isKabag = \App\Support\ModeKerja::ketuaAktif(auth()->user());
    $createRoute = $isKabag ? 'kabag.laporan.create' : 'laporan.create';
    $editRoute = $isKabag ? 'kabag.laporan.edit' : 'laporan.edit';
    $showRoute = $isKabag ? 'kabag.laporan.show' : 'laporan.show';
    $indexRoute = $isKabag ? 'kabag.laporan.index' : 'laporan.index';

    $filterAktif =
        request()->hasAny(['cari', 'status', 'periode']) &&
        collect(['cari', 'status', 'periode'])->contains(fn($k) => request()->filled($k));

    $aksenStatus = [
        'menunggu' => 'border-l-amber-400',
        'disetujui' => 'border-l-green-500',
        'dikembalikan' => 'border-l-red-500',
    ];
@endphp

<x-app-layout>

    <style>
        @keyframes bps-up {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .bps .fx {
            opacity: 0;
            animation: bps-up .55s cubic-bezier(.2, .7, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        .bps .lift {
            transition: transform .25s, box-shadow .25s, border-color .25s;
        }

        .bps .lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -14px rgb(31 56 100 / .3);
        }

        @media (prefers-reduced-motion: reduce) {

            .bps *,
            .bps *::before {
                animation: none !important;
                transition: none !important;
            }

            .bps .fx {
                opacity: 1;
            }
        }
    </style>

    <div class="bps max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- Header --}}
        <div class="fx flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5" style="--d:0s">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Laporan Harian</h1>
                <p class="text-sm text-gray-500 mt-1">Riwayat laporan aktivitas yang telah Anda buat.</p>
            </div>
            <x-action-button href="{{ route($createRoute) }}">Buat Laporan</x-action-button>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route($indexRoute) }}"
            class="fx bg-white rounded-2xl border border-gray-200 p-4 mb-5" style="--d:.08s">
            <div class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <label for="cari" class="text-xs text-gray-500 block mb-1">Cari</label>
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                        </svg>
                        <input id="cari" type="text" name="cari" value="{{ request('cari') }}"
                            placeholder="Cari uraian atau pekerjaan..."
                            class="w-full text-sm rounded-lg border-gray-300 pl-9 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                </div>

                <div class="md:w-44">
                    <label for="status" class="text-xs text-gray-500 block mb-1">Status</label>
                    <select id="status" name="status"
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">Semua status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                    </select>
                </div>

                <div class="md:w-44">
                    <label for="periode" class="text-xs text-gray-500 block mb-1">Bulan - Tahun</label>
                    <input id="periode" type="month" name="periode" value="{{ request('periode') }}"
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white px-4 py-2 text-sm font-medium transition-colors">
                        Filter
                    </button>
                    {{-- @if ($filterAktif)
                        <a href="{{ route($indexRoute) }}"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    @endif --}}
                </div>
            </div>
        </form>

        {{-- Hasil --}}
        @if ($laporan->isEmpty())
            <div class="fx bg-white rounded-2xl border border-gray-200 text-center py-16 px-4" style="--d:.15s">
                <span class="inline-flex w-12 h-12 rounded-2xl bg-gray-50 text-gray-300 items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <p class="text-sm font-medium text-gray-900 mt-3">
                    {{ $filterAktif ? 'Tidak ada laporan yang cocok dengan filter ini' : 'Belum ada laporan' }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $filterAktif ? 'Coba ubah kata kunci, status, atau periode.' : 'Mulai buat laporan aktivitas harian pertama Anda.' }}
                </p>
                @if ($filterAktif)
                    <a href="{{ route($indexRoute) }}"
                        class="inline-block mt-4 text-sm text-[#1F3864] font-medium hover:underline">Reset filter</a>
                @else
                    <a href="{{ route($createRoute) }}"
                        class="inline-block mt-4 text-sm text-[#1F3864] font-medium hover:underline">Buat laporan</a>
                @endif
            </div>
        @else
            <p class="fx text-xs text-gray-500 mb-2 px-1" style="--d:.12s">
                <span class="font-semibold text-gray-700 tabular-nums">{{ $laporan->total() }}</span> laporan
                @if (request('periode'))
                    &middot; {{ \Carbon\Carbon::createFromFormat('Y-m', request('periode'))->translatedFormat('F Y') }}
                @endif
            </p>

            {{-- Mobile: kartu --}}
            <div class="md:hidden space-y-2.5">
                @foreach ($laporan as $i => $item)
                    @php $perluAksi = in_array($item->status, ['draft', 'dikembalikan']); @endphp
                    <a href="{{ route($perluAksi ? $editRoute : $showRoute, $item) }}"
                        class="fx lift block bg-white border border-gray-200 border-l-4 {{ $aksenStatus[$item->status] ?? 'border-l-gray-300' }} rounded-xl p-4"
                        style="--d:{{ 0.15 + min($i, 8) * 0.04 }}s">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs text-gray-500 tabular-nums">{{ $item->tanggal->format('d M Y') }}</p>
                            <x-status-badge :status="$item->status" />
                        </div>
                        @if ($item->tugas?->judul)
                            <p class="text-sm font-medium text-gray-900 mt-2 truncate">{{ $item->tugas->judul }}</p>
                        @endif
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $item->uraian }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <span
                                class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $item->lokasi) }}</span>
                            <span
                                class="text-xs font-semibold {{ $item->status === 'dikembalikan' ? 'text-red-600' : 'text-[#1F3864]' }}">
                                {{ $item->status === 'dikembalikan' ? 'Revisi →' : ($perluAksi ? 'Edit →' : 'Lihat →') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Desktop: tabel --}}
            <div class="fx hidden md:block bg-white rounded-2xl border border-gray-200 overflow-hidden"
                style="--d:.15s">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Tanggal</th>
                                <th class="py-3 px-5 font-medium">Uraian</th>
                                <th class="py-3 px-5 font-medium">Lokasi</th>
                                <th class="py-3 px-5 font-medium">Status</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($laporan as $i => $item)
                                @php $perluAksi = in_array($item->status, ['draft', 'dikembalikan']); @endphp
                                <tr class="fx group hover:bg-blue-50/40 transition-colors"
                                    style="--d:{{ 0.2 + min($i, 10) * 0.035 }}s">
                                    <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap tabular-nums">
                                        {{ $item->tanggal->format('d M Y') }}</td>
                                    <td class="py-3.5 px-5 max-w-sm">
                                        @if ($item->tugas?->judul)
                                            <p class="font-medium text-gray-900 truncate">{{ $item->tugas->judul }}</p>
                                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                                {{ Str::limit($item->uraian, 60) }}</p>
                                        @else
                                            <p class="text-gray-900 truncate">{{ Str::limit($item->uraian, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-500 capitalize whitespace-nowrap">
                                        {{ str_replace('_', ' ', $item->lokasi) }}</td>
                                    <td class="py-3.5 px-5"><x-status-badge :status="$item->status" /></td>
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        @if ($perluAksi)
                                            <a href="{{ route($editRoute, $item) }}"
                                                class="inline-flex items-center gap-1 text-sm font-semibold {{ $item->status === 'dikembalikan' ? 'text-red-600' : 'text-[#1F3864]' }}">
                                                {{ $item->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                                                <span class="transition-transform group-hover:translate-x-1">→</span>
                                            </a>
                                        @else
                                            <a href="{{ route($showRoute, $item) }}" 
                                            
                                                class="inline-flex items-center gap-1 text-sm font-medium text-[#1F3864]">
                                                Lihat
                                                <span class="transition-transform group-hover:translate-x-1">→</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 px-1">
                {{ $laporan->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
