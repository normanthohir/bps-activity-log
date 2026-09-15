{{-- resources/views/dashboard/staf.blade.php --}}
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

        .delay-3 {
            animation-delay: .15s;
        }

        .delay-4 {
            animation-delay: .2s;
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

        {{-- Greeting card --}}
        <div
            class="relative overflow-hidden bg-gradient-to-br from-[#1F3864] to-[#2A4A82] rounded-2xl p-6 sm:p-8 mb-6 text-white animate-in">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide mb-1">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold">Halo, {{ auth()->user()->name }}</h1>
                    <p class="text-white/70 text-sm mt-1">{{ auth()->user()->bagian->nama_bagian }}</p>
                </div>
                <x-action-button href="{{ route('laporan.create') }}" variant="light">
                    Laporan Baru
                </x-action-button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div
                class="animate-in delay-1 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tugas Aktif</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $tugasAktif }}</p>
                    </div>
                </div>
            </div>

            <div
                class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu Approval</p>
                        <p
                            class="text-2xl font-semibold {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                            {{ $menungguApproval }}</p>
                    </div>
                </div>
            </div>

            <div
                class="animate-in delay-3 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Laporan Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $laporanBulanIni }}</p>
                    </div>
                </div>
            </div>

            <div
                class="animate-in delay-4 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Disetujui Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $disetujuiBulanIni }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom kiri: riwayat laporan --}}
            <div class="lg:col-span-2">
                <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div
                        class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <h2 class="font-semibold text-gray-900">Riwayat Laporan Terbaru</h2>
                        <a href="{{ route('laporan.index') }}"
                            class="text-sm text-[#1F3864] hover:underline font-medium">Lihat Semua</a>
                    </div>

                    @if ($laporanTerbaru->isEmpty())
                        <div class="text-center py-12 px-4">
                            <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan</p>
                            <p class="text-sm text-gray-500 mt-1">Mulai buat laporan aktivitas harian pertama Anda.</p>
                            <x-action-button href="{{ route('laporan.create') }}" class="mt-4">
                                Buat Laporan
                            </x-action-button>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                                        <th class="py-2.5 px-5 font-medium">Tanggal</th>
                                        <th class="py-2.5 px-5 font-medium">Uraian</th>
                                        <th class="py-2.5 px-5 font-medium text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($laporanTerbaru as $i => $laporan)
                                        <tr class="animate-row hover:bg-gray-50/70 transition-colors"
                                            style="animation-delay: {{ $i * 0.05 }}s">
                                            <td class="py-3 px-5 text-gray-500 whitespace-nowrap">
                                                {{ $laporan->tanggal->format('d M Y') }}</td>
                                            <td class="py-3 px-5 text-gray-900">{{ Str::limit($laporan->uraian, 50) }}
                                            </td>
                                            <td class="py-3 px-5 text-right">
                                                <x-status-badge :status="$laporan->status" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom kanan: tugas aktif --}}
            <div>
                <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Tugas Aktif</h2>
                        <a href="{{ route('tugas.index') }}"
                            class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($tugasAktifList->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Tidak ada tugas aktif saat ini.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasAktifList as $tugas)
                                <a href="{{ route('tugas.show', $tugas) }}"
                                    class="block px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $tugas->judul }}</p>
                                        @if ($tugas->status === 'dikerjakan')
                                            <span
                                                class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-[10px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset ring-blue-600/20 shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Dikerjakan
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset ring-gray-500/20 shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Belum
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        dari {{ $tugas->pemberiTugas->name }}
                                        @if ($tugas->tenggat)
                                            &middot; tenggat {{ $tugas->tenggat->format('d M') }}
                                        @endif
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
