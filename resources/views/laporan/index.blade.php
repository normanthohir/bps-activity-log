{{-- resources/views/laporan/index.blade.php --}}
@php
    $isKabag = auth()->user()->role === 'kepala_bagian';
    $createRoute = $isKabag ? 'kabag.laporan.create' : 'laporan.create';
    $editRoute = $isKabag ? 'kabag.laporan.edit' : 'laporan.edit';
    $showRoute = $isKabag ? 'kabag.laporan.show' : 'laporan.show';
    $indexRoute = $isKabag ? 'kabag.laporan.index' : 'laporan.index';
@endphp
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
                <h1 class="text-xl font-semibold text-gray-900">Daftar Laporan Harian</h1>
                <p class="text-sm text-gray-500 mt-0.5">Riwayat laporan aktivitas yang telah Anda buat.</p>
            </div>
            <x-action-button href="{{ route($createRoute) }}">
                Laporan Baru
            </x-action-button>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route($indexRoute) }}"
            class="animate-in delay-1 flex flex-wrap items-center gap-3 mb-5">
            <select name="bulan" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Bulan</option>
                @foreach (['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $angka => $nama)
                    <option value="{{ $angka }}" @selected($bulan == $angka)>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="tahun" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Tahun</option>
                @for ($t = now()->year; $t >= now()->year - 3; $t--)
                    <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                @endfor
            </select>

            <select name="status" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
            </select>

            @if (request()->hasAny(['status', 'bulan', 'tahun']))
                <a href="{{ route($indexRoute) }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>

        {{-- Table card --}}
        <div class="animate-in delay-1 bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($laporan->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">
                        {{ request('status') ? 'Tidak ada laporan dengan status ini' : 'Belum ada laporan' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ request('status') ? 'Coba pilih status lain.' : 'Mulai buat laporan aktivitas harian pertama Anda.' }}
                    </p>
                </div>
            @else
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
                                <tr class="animate-row hover:bg-gray-50/70 transition-colors"
                                    style="animation-delay: {{ min($i, 10) * 0.04 }}s">
                                    <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">
                                        {{ $item->tanggal->format('d M Y') }}</td>
                                    <td class="py-3.5 px-5 text-gray-900">{{ Str::limit($item->uraian, 45) }}</td>
                                    <td class="py-3.5 px-5 text-gray-500 capitalize">
                                        {{ str_replace('_', ' ', $item->lokasi) }}</td>
                                    <td class="py-3.5 px-5">
                                        <x-status-badge :status="$item->status" />
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        @if (in_array($item->status, ['draft', 'dikembalikan']))
                                            <a href="{{ route($editRoute, $item) }}"
                                                class="text-[#1F3864] hover:underline text-sm font-medium">
                                                {{ $item->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                                            </a>
                                        @else
                                            <a href="{{ route($showRoute, $item) }}"
                                                class="text-[#1F3864] hover:underline text-sm font-medium">
                                                Lihat
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $laporan->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
