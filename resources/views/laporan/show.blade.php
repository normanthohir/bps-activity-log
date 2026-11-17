s@php
    $routeIndex = 'laporan.index';
    $routeEdit = 'laporan.edit';

    // Boleh diubah selama masih draft atau dikembalikan (sesuai LaporanHarianPolicy)
    $bisaDiubah = in_array($laporan->status, ['draft', 'dikembalikan']);
    $logTerakhir = $laporan->logApproval->last();
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

        .delay-2 {
            animation-delay: .1s;
        }

        .delay-3 {
            animation-delay: .15s;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-in {
                animation: none;
            }
        }
    </style>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Tombol kembali --}}
        <a href="{{ route($routeIndex) }}"
            class="animate-in inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar laporan
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

            {{-- Kiri: isi laporan (tanpa identitas pegawai, karena milik sendiri) --}}
            <div class="lg:col-span-2">
                @include('laporan.partials.isi-laporan', ['tampilPegawai' => false])
            </div>

            {{-- Kanan: tindakan, catatan atasan, dan riwayat --}}
            <div class="space-y-4 lg:sticky lg:top-6">

                {{-- Tindakan --}}
                <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Laporan saya</p>

                    @if ($bisaDiubah)
                        <a href="{{ route($routeEdit, $laporan) }}"
                            class="group flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 active:scale-95 motion-reduce:transition-none
                                {{ $laporan->status === 'dikembalikan' ? 'bg-red-600 hover:bg-red-700' : 'bg-[#1F3864] hover:bg-[#16294a]' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                            {{ $laporan->status === 'dikembalikan' ? 'Revisi laporan' : 'Edit laporan' }}
                        </a>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $laporan->status === 'dikembalikan'
                                ? 'Perbaiki sesuai catatan atasan, lalu kirim ulang.'
                                : 'Laporan masih draft dan belum dikirim ke atasan.' }}
                        </p>
                    @elseif ($laporan->status === 'menunggu')
                        <div class="flex items-start gap-2.5 rounded-lg bg-amber-50 px-3 py-2.5 text-sm text-amber-800">
                            <span
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-amber-400 animate-pulse motion-reduce:animate-none"></span>
                            Laporan sudah dikirim dan sedang menunggu keputusan atasan. Belum bisa diubah.
                        </div>
                    @else
                        <div class="flex items-start gap-2.5 rounded-lg bg-green-50 px-3 py-2.5 text-sm text-green-800">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Laporan sudah disetujui dan tidak dapat diubah.
                        </div>
                    @endif
                </div>

                {{-- Catatan penolakan dari atasan --}}
                @if ($laporan->status === 'dikembalikan' && $logTerakhir)
                    <div class="animate-in delay-2 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-red-800">Dikembalikan, mohon direvisi</p>
                            <p class="mt-1 text-sm text-red-700">{{ $logTerakhir->catatan ?? 'Tidak ada catatan.' }}</p>
                            <p class="mt-2 text-xs text-red-500">
                                {{ $logTerakhir->approver->name ?? '-' }} &middot;
                                {{ $logTerakhir->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Riwayat --}}
                <div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-4">Riwayat laporan</p>

                    <ol class="relative border-s border-gray-200 ms-2 space-y-5">
                        <li class="ms-5">
                            <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                            <p class="text-sm font-medium text-gray-900">Laporan dibuat</p>
                            <p class="text-xs text-gray-400">{{ $laporan->created_at->format('d M Y, H:i') }}</p>
                        </li>

                        @foreach ($laporan->logApproval as $log)
                            @php $disetujui = $log->aksi === 'disetujui'; @endphp
                            <li class="ms-5">
                                <span
                                    class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full {{ $disetujui ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $disetujui ? 'Disetujui' : 'Dikembalikan' }} oleh
                                        {{ $log->approver->name ?? '-' }}
                                    </p>
                                    <span
                                        class="text-[11px] px-1.5 py-0.5 rounded-full {{ $disetujui ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $log->approver && $log->approver->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</p>
                                @if ($log->catatan)
                                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 mt-2">
                                        {{ $log->catatan }}</p>
                                @endif
                            </li>
                        @endforeach

                        @if ($laporan->status === 'menunggu')
                            <li class="ms-5">
                                <span
                                    class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse motion-reduce:animate-none"></span>
                                <p class="text-sm font-medium text-amber-700">Menunggu persetujuan atasan</p>
                            </li>
                        @endif
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
