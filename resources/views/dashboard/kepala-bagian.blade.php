{{-- resources/views/dashboard/kepala-bagian.blade.php (mode Ketua Tim) --}}
@php
    $user = auth()->user();
    $jumlahStaf = $staf->count();
    $sudahLapor = $jumlahStaf - $belumLapor->count();
    $persenLapor = $jumlahStaf > 0 ? round($sudahLapor / $jumlahStaf * 100) : 0;

    $totalTugasTim = $tugasTimSelesai + $tugasTimDikerjakan + $tugasTimBelum;
    $tugasAktifTim = $tugasTimDikerjakan + $tugasTimBelum;
    $pctSelesai = $totalTugasTim > 0 ? round($tugasTimSelesai / $totalTugasTim * 100) : 0;
    $pctDikerjakan = $totalTugasTim > 0 ? round($tugasTimDikerjakan / $totalTugasTim * 100) : 0;
    $pctBelum = $totalTugasTim > 0 ? max(0, 100 - $pctSelesai - $pctDikerjakan) : 0;

    $kartu = [
        ['Anggota tim', $jumlahStaf, $akhirPekan ? 'akhir pekan' : $sudahLapor . ' sudah lapor hari ini', 'bg-blue-50 text-blue-600', 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', route('kabag.tugas.index')],
        ['Menunggu persetujuan', $menungguApproval, 'laporan tim', 'bg-amber-50 text-amber-600', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', route('kabag.approval.index')],
        ['Tingkat persetujuan', $tingkatPersetujuan !== null ? $tingkatPersetujuan . '%' : '-', 'bulan ini', 'bg-green-50 text-green-600', 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941', route('kabag.approval.index')],
        ['Tugas tim aktif', $tugasAktifTim, $tugasTerlambat > 0 ? $tugasTerlambat . ' terlambat' : 'tidak ada yang terlambat', $tugasTerlambat > 0 ? 'bg-red-50 text-red-600' : 'bg-indigo-50 text-indigo-600', 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z', route('kabag.tugas.index')],
    ];
@endphp

<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[#14264a] to-[#1F3864] rounded-2xl p-6 sm:p-8 mb-6 text-white animate-in">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-16 -right-24 w-56 h-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-400/20 text-amber-100 ring-1 ring-inset ring-amber-300/30">
                            Mode Ketua Tim
                        </span>
                        <span class="text-white/60 text-xs">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <h1 class="text-2xl font-semibold">Halo, {{ $user->name }}</h1>
                    <p class="text-white/70 text-sm mt-1">
                        {{ $namaTim ?: $bagian?->nama_bagian ?? 'Belum memimpin tim' }} &middot; {{ $jumlahStaf }} anggota
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <x-action-button href="{{ route('pekerjaan.create') }}" variant="light">Pekerjaan / disposisi baru</x-action-button>
                </div>
            </div>

            <p class="relative mt-4 text-xs text-white/60">
                Laporan harian pribadi dan tugas yang diberikan kepada Anda diisi di mode Anggota.
            </p>
        </div>

        {{-- Perlu tindakan --}}
        @if ($menungguApproval > 0 || $tugasTerlambat > 0)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 animate-in">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    <p class="text-sm font-semibold text-amber-900">Perlu tindakan Anda</p>
                </div>
                <ul class="space-y-1.5 text-sm">
                    @if ($menungguApproval > 0)
                        <li class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-amber-900"><b>{{ $menungguApproval }}</b> laporan tim menunggu persetujuan.</span>
                            <a href="{{ route('kabag.approval.index') }}" class="text-xs font-semibold text-amber-800 hover:underline">Proses sekarang &rarr;</a>
                        </li>
                    @endif
                    @if ($tugasTerlambat > 0)
                        <li class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-amber-900"><b>{{ $tugasTerlambat }}</b> tugas tim sudah melewati tenggat.</span>
                            <a href="{{ route('kabag.tugas.index') }}" class="text-xs font-semibold text-amber-800 hover:underline">Lihat tugas &rarr;</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        @include('dashboard.partials.kalender-pekerjaan')

        {{-- Statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ($kartu as $i => [$label, $nilai, $sub, $warna, $ikon, $tautan])
                <a href="{{ $tautan }}"
                    class="animate-in delay-{{ $i + 1 }} bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md hover:-translate-y-0.5">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center {{ $warna }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" /></svg>
                    </span>
                    <p class="text-2xl font-semibold text-gray-900 mt-3">{{ $nilai }}</p>
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $sub }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom kiri --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Laporan hari ini --}}
                <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900">Laporan hari ini</h2>
                        @unless ($akhirPekan)
                            <span class="text-xs text-gray-500">{{ $sudahLapor }} dari {{ $jumlahStaf }} anggota</span>
                        @endunless
                    </div>

                    @if ($akhirPekan)
                        <p class="text-sm text-gray-400 py-2">Hari ini akhir pekan, tidak ada kewajiban lapor.</p>
                    @elseif ($jumlahStaf === 0)
                        <p class="text-sm text-gray-400 py-2">Belum ada anggota di tim Anda.</p>
                    @else
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-4">
                            <div class="h-full rounded-full bg-green-500 animate-bar" style="width: {{ $persenLapor }}%"></div>
                        </div>

                        @if ($belumLapor->isEmpty())
                            <p class="flex items-center gap-2 text-sm text-green-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Semua anggota sudah mengirim laporan hari ini.
                            </p>
                        @else
                            <p class="text-xs font-medium text-gray-500 mb-2">Belum melapor ({{ $belumLapor->count() }})</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($belumLapor->take(8) as $p)
                                    <span class="inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full pl-1 pr-3 py-1 text-xs text-gray-700">
                                        <span class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-[11px] font-semibold flex items-center justify-center">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                        {{ $p->name }}
                                    </span>
                                @endforeach
                                @if ($belumLapor->count() > 8)
                                    <span class="inline-flex items-center text-xs text-gray-500 px-2">+{{ $belumLapor->count() - 8 }} lainnya</span>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Progress tugas tim --}}
                <div class="animate-in delay-2 bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="font-semibold text-gray-900 mb-3">Progress tugas tim</h2>

                    @if ($totalTugasTim > 0)
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden flex">
                            <div class="h-full bg-green-500 animate-bar" style="width: {{ $pctSelesai }}%"></div>
                            <div class="h-full bg-blue-500 animate-bar" style="width: {{ $pctDikerjakan }}%"></div>
                            <div class="h-full bg-gray-300 animate-bar" style="width: {{ $pctBelum }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-3 text-xs text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> Selesai ({{ $tugasTimSelesai }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Dikerjakan ({{ $tugasTimDikerjakan }})</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-300"></span> Belum dikerjakan ({{ $tugasTimBelum }})</span>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-2">Belum ada tugas yang diberikan ke tim.</p>
                    @endif
                </div>

                {{-- Tugas tim terbaru --}}
                <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Tugas tim terbaru</h2>
                        <a href="{{ route('kabag.tugas.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Lihat semua</a>
                    </div>

                    @if ($tugasTimTerbaru->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-10 px-4">Belum ada tugas yang diberikan.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasTimTerbaru as $tugas)
                                @php
                                    $badge = match ($tugas->status) {
                                        'selesai' => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500', 'Selesai'],
                                        'dikerjakan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500', 'Dikerjakan'],
                                        default => ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400', 'Belum dikerjakan'],
                                    };
                                @endphp
                                <a href="{{ route('kabag.tugas.show', $tugas) }}"
                                    class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-gray-50/70 transition-colors group">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 text-sm truncate group-hover:text-[#1F3864]">{{ $tugas->judul }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">
                                            {{ $tugas->penerimaTugas?->name ?? '-' }}
                                            @if ($tugas->tenggat)
                                                &middot; tenggat {{ $tugas->tenggat->format('d M Y') }}
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

            {{-- Kolom kanan --}}
            <div class="space-y-6">

                {{-- Menunggu persetujuan --}}
                <div class="animate-in delay-3 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Menunggu persetujuan</h2>
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

                {{-- Tugas aktif milik ketua sendiri --}}
                <div class="animate-in delay-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900 text-sm">Tugas aktif saya</h2>
                        <a href="{{ route('kabag.tugas-aktif.index') }}" class="text-xs font-medium text-[#1F3864] hover:underline">Semua</a>
                    </div>

                    @if ($tugasSaya->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8 px-4">Tidak ada tugas aktif untuk Anda.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($tugasSaya as $tugas)
                                <a href="{{ route('kabag.tugas-aktif.show', $tugas) }}"
                                    class="block px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tugas->judul }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        dari {{ $tugas->pemberiTugas?->name ?? '-' }}
                                        @if ($tugas->tenggat)
                                            &middot; {{ $tugas->tenggat->format('d M') }}
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