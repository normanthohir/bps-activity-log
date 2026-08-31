{{-- resources/views/dashboard/kepala-bagian.blade.php --}}
<x-app-layout>
    <style>
        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in {
            animation: fade-slide-up 0.5s ease-out both;
        }
        .delay-1 { animation-delay: .05s; }
        .delay-2 { animation-delay: .1s; }
        .delay-3 { animation-delay: .15s; }
        .delay-4 { animation-delay: .2s; }
        .delay-5 { animation-delay: .25s; }
        .delay-6 { animation-delay: .3s; }

        @keyframes grow-bar {
            from { width: 0%; }
        }
        .animate-bar {
            animation: grow-bar 0.8s ease-out .3s both;
        }
    </style>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Greeting card --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#1F3864] to-[#2A4A82] rounded-2xl p-6 sm:p-8 mb-6 text-white animate-in">
            {{-- Decorative circles --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide mb-1">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                    <h1 class="text-2xl font-semibold">Halo, {{ auth()->user()->name }}</h1>
                    <p class="text-white/70 text-sm mt-1">Kepala Seksi &middot; {{ $bagian->nama_bagian }} &middot; {{ $staf->count() }} staf</p>
                </div>
                <a href="{{ route('kabag.tugas.create') }}"
                   class="relative inline-flex items-center gap-1.5 bg-white text-[#1F3864] text-sm font-semibold px-4 py-2.5 rounded-lg transition-all hover:bg-white/90 hover:scale-[1.03] active:scale-[0.98] shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Beri Tugas
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="animate-in delay-1 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Staf</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $staf->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu Persetujuan</p>
                        <p class="text-2xl font-semibold {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $menungguApproval }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tugas Tim</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $tugasTimDikerjakan + $tugasTimBelum }}</p>
                    </div>
                </div>
            </div>

            <div class="animate-in delay-4 bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tugas Selesai</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $tugasTimSelesai }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom kiri: Tugas Tim --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Progress tugas tim --}}
                @php
                    $totalTugasTim = $tugasTimSelesai + $tugasTimDikerjakan + $tugasTimBelum;
                    $pctSelesai = $totalTugasTim > 0 ? round($tugasTimSelesai / $totalTugasTim * 100) : 0;
                    $pctDikerjakan = $totalTugasTim > 0 ? round($tugasTimDikerjakan / $totalTugasTim * 100) : 0;
                    $pctBelum = $totalTugasTim > 0 ? max(0, 100 - $pctSelesai - $pctDikerjakan) : 0;
                @endphp
                <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900">Progress Tugas Tim</h2>
                        <span class="text-xs text-gray-500">{{ $totalTugasTim }} total tugas</span>
                    </div>

                    @if ($totalTugasTim > 0)
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden flex">
                            <div class="h-full bg-green-500 animate-bar" style="width: {{ $pctSelesai }}%"></div>
                            <div class="h-full bg-blue-500 animate-bar" style="width: {{ $pctDikerjakan }}%"></div>
                            <div class="h-full bg-gray-300 animate-bar" style="width: {{ $pctBelum }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-3 text-xs text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> Selesai ({{ $tugasTimSelesai }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Dikerjakan ({{ $tugasTimDikerjakan }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-300"></span> Belum Dikerjakan ({{ $tugasTimBelum }})</span>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-2">Belum ada tugas yang diberikan ke tim.</p>
                    @endif
                </div>

                {{-- Daftar tugas tim terbaru --}}
                <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Tugas Tim Terbaru</h2>
                        <a href="{{ route('kabag.tugas.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Lihat semua</a>
                    </div>

                    @if ($tugasTimTerbaru->isEmpty())
                        <div class="text-center py-10 px-4">
                            <p class="text-sm text-gray-400">Belum ada tugas yang diberikan.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasTimTerbaru as $tugas)
                                @php
                                    $badge = match ($tugas->status) {
                                        'selesai' => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500', 'Selesai'],
                                        'dikerjakan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500', 'Dikerjakan'],
                                        default => ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400', 'Belum Dikerjakan'],
                                    };
                                @endphp
                                <a href="{{ route('kabag.tugas.show', $tugas) }}"
                                   class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-gray-50/70 transition-colors group">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 text-sm truncate group-hover:text-[#1F3864] transition-colors">{{ $tugas->judul }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $tugas->penerimaTugas->name }}
                                            @if ($tugas->tenggat)
                                                &middot; Tenggat {{ $tugas->tenggat->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 {{ $badge[0] }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                        {{ $badge[2] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom kanan: Tugas saya & Laporan menunggu --}}
            <div class="space-y-6">

                {{-- Tugas milik kepala bagian sendiri --}}
                <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Tugas Saya</h2>
                        <a href="{{ route('tugas.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($tugasSaya->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Tidak ada tugas aktif untuk Anda.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasSaya as $tugas)
                                <a href="{{ route('tugas.show', $tugas) }}" class="block px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tugas->judul }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        dari {{ $tugas->pemberiTugas->name }}
                                        @if ($tugas->tenggat)
                                            &middot; {{ $tugas->tenggat->format('d M') }}
                                        @endif
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Laporan menunggu persetujuan --}}
                <div class="animate-in delay-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Menunggu Persetujuan</h2>
                        <a href="{{ route('kabag.approval.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($laporanMenunggu->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Tidak ada laporan menunggu.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($laporanMenunggu as $laporan)
                                <a href="{{ route('kabag.approval.show', $laporan) }}"
                                   class="flex items-center gap-2.5 px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <span class="w-7 h-7 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $laporan->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $laporan->tanggal->format('d M Y') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            <a href="{{ route('kabag.tugas.index') }}"
               class="animate-in delay-5 bg-white border border-gray-200 rounded-xl p-5 hover:border-[#1F3864]/30 hover:shadow-sm hover:-translate-y-0.5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#DCE6F1] flex items-center justify-center group-hover:bg-[#1F3864] transition-colors">
                        <svg class="w-5 h-5 text-[#1F3864] group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Tugas Tim</p>
                        <p class="text-sm text-gray-500">Buat dan kelola tugas untuk staf</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('kabag.approval.index') }}"
               class="animate-in delay-6 bg-white border border-gray-200 rounded-xl p-5 hover:border-[#1F3864]/30 hover:shadow-sm hover:-translate-y-0.5 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#DCE6F1] flex items-center justify-center group-hover:bg-[#1F3864] transition-colors">
                        <svg class="w-5 h-5 text-[#1F3864] group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Persetujuan Laporan</p>
                        <p class="text-sm text-gray-500">Setujui atau kembalikan laporan staf</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</x-app-layout>