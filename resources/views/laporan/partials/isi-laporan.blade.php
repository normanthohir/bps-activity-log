@php
    $t = $laporan->tugas;
    $tampilPegawai = $tampilPegawai ?? true;
    $bolehBuka = $t && \App\Support\AksesPekerjaan::dapatLihat(auth()->user(), $t);

    $outputGaya = match ($laporan->output) {
        'selesai' => ['box' => 'bg-green-50 border-green-200', 'judul' => 'text-green-800', 'isi' => 'text-green-700',
                      'ikon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Selesai'],
        'gagal' => ['box' => 'bg-red-50 border-red-200', 'judul' => 'text-red-800', 'isi' => 'text-red-700',
                    'ikon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Gagal'],
        'tertunda' => ['box' => 'bg-amber-50 border-amber-200', 'judul' => 'text-amber-800', 'isi' => 'text-amber-700',
                       'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Tertunda'],
        default => ['box' => 'bg-gray-50 border-gray-200', 'judul' => 'text-gray-800', 'isi' => 'text-gray-600',
                    'ikon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => $laporan->output ?? '-'],
    };

    $lokasiGaya = match ($laporan->lokasi) {
        'kantor' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'lapangan' => 'bg-green-50 text-green-700 ring-green-600/20',
        'dinas_luar' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        default => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    };

    if ($t) {
        $badgeTugas = match ($t->status) {
            'selesai'    => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500', 'Selesai'],
            'dikerjakan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500', 'Dikerjakan'],
            default      => ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400', 'Belum dikerjakan'],
        };

        // Terlambat: pekerjaan dilaporkan selesai setelah tenggat.
        // Acuan tanggal selesai = tanggal laporan. Jika tidak terlambat, tidak ada yang ditampilkan.
        $selesai = $t->status === 'selesai' || $laporan->output === 'selesai';
        $terlambat = $selesai && $t->tenggat
            && $laporan->tanggal->copy()->startOfDay()->gt($t->tenggat->copy()->startOfDay());
        $hariTerlambat = $terlambat
            ? (int) abs($t->tenggat->copy()->startOfDay()->diffInDays($laporan->tanggal->copy()->startOfDay()))
            : 0;
    }

    $judulBagian = 'flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500';
@endphp

<div class="space-y-4">

    {{-- 1. Identitas laporan --}}
    <section class="animate-in delay-1 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
            @if ($tampilPegawai)
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#DCE6F1] text-base font-semibold text-[#1F3864]">
                        {{ strtoupper(substr($laporan->user->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="text-lg font-semibold leading-tight text-gray-900">{{ $laporan->user->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $laporan->user->bagian->nama_bagian ?? '-' }}
                            &middot; {{ $laporan->user->role === 'kepala_bagian' ? 'Kepala Tim' : 'Staf' }}
                        </p>
                    </div>
                </div>
            @else
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Detail Laporan</h1>
                    {{-- <p class="text-sm text-gray-500">{{ $laporan->tanggal->translatedFormat('l, d F Y') }}</p> --}}
                </div>
            @endif
            <x-status-badge :status="$laporan->status" />
        </div>

        <dl class="grid grid-cols-2 divide-x divide-gray-100 border-t border-gray-100 bg-gray-50/60">
            <div class="px-5 py-3">
                <dt class="mb-1 flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    Tanggal laporan
                </dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $laporan->tanggal->translatedFormat('l, d F Y') }}</dd>
            </div>
            <div class="px-5 py-3">
                <dt class="mb-1 flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    Lokasi
                </dt>
                <dd>
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $lokasiGaya }}">
                        {{ ucwords(str_replace('_', ' ', $laporan->lokasi)) }}
                    </span>
                </dd>
            </div>
        </dl>

        {{-- Hanya tampil bila terlambat --}}
        @if ($t && $terlambat)
            <div class="flex items-start gap-2.5 border-t border-red-100 bg-red-50 px-5 py-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-sm text-red-800">
                    <span class="font-semibold">Pekerjaan selesai terlambat {{ $hariTerlambat }} hari.</span>
                    <span class="text-red-700">
                        Tenggat {{ $t->tenggat->format('d/m/Y') }}, dilaporkan selesai {{ $laporan->tanggal->format('d/m/Y') }}.
                    </span>
                </p>
            </div>
        @endif
    </section>

    {{-- 2. Pekerjaan terkait --}}
    <section class="animate-in delay-2 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 bg-gray-50/60 px-5 py-3">
            <h2 class="{{ $judulBagian }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                Pekerjaan terkait
            </h2>
            @if ($bolehBuka)
                <a href="{{ route('pekerjaan.show', $t) }}" class="group inline-flex items-center gap-1 text-xs font-semibold text-[#1F3864]">
                    Buka pekerjaan
                    <span class="transition-transform group-hover:translate-x-1 motion-reduce:transition-none">→</span>
                </a>
            @endif
        </div>

        @if (!$t)
            <p class="px-5 py-6 text-sm text-gray-500">Laporan mandiri, tidak terkait pekerjaan dari atasan.</p>
        @else
            <div class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-base font-semibold text-gray-900">{{ $t->judul }}</p>
                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeTugas[0] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $badgeTugas[1] }}"></span>{{ $badgeTugas[2] }}
                    </span>
                </div>

                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500">Tim</dt>
                        <dd class="font-medium text-gray-800">{{ $t->bagian?->nama_bagian ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Pelaksana</dt>
                        <dd class="font-medium text-gray-800">{{ $t->penerimaTugas?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Diberikan oleh</dt>
                        <dd class="font-medium text-gray-800">
                            {{ $t->pemberiTugas?->name ?? '-' }}
                            @if ($t->pemberiTugas)
                                <span class="font-normal text-gray-500">({{ $t->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Periode</dt>
                        <dd class="font-medium tabular-nums text-gray-800">
                            {{ $t->tanggal_mulai?->format('d/m/Y') ?? '-' }} &ndash; {{ $t->tenggat?->format('d/m/Y') ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Input, Proses, Output: bisa dibuka-tutup tanpa JavaScript --}}
            <div class="divide-y divide-gray-100 border-t border-gray-100">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                        <span class="text-sm font-semibold text-blue-900">
                            1 · Input
                            @if ($t->jenis_input)<span class="ml-1 text-[11px] font-normal uppercase text-gray-400">{{ $t->jenis_input }}</span>@endif
                        </span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <div class="space-y-2 px-5 pb-4 text-sm text-gray-700">
                        @if ($t->jenis_input === 'surat')
                            <p class="text-xs text-gray-500">{{ $t->asal_surat }} &middot; {{ $t->nomor_surat }} &middot; {{ $t->tanggal_surat?->format('d/m/Y') }}</p>
                        @endif
                        <p class="whitespace-pre-wrap leading-relaxed">{{ $t->uraian_input ?: 'Belum ada uraian input.' }}</p>
                        @if ($t->file_input && $bolehBuka)
                            <a href="{{ route('pekerjaan.berkas', [$t, 'input']) }}" class="inline-flex text-xs font-medium text-blue-700 underline">Unduh lampiran input</a>
                        @endif
                    </div>
                </details>

                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                        <span class="text-sm font-semibold text-blue-900">2 · Proses</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <div class="px-5 pb-4 text-sm text-gray-700">
                        <p class="whitespace-pre-wrap leading-relaxed">{{ $t->deskripsi ?: 'Tidak ada rincian proses.' }}</p>
                    </div>
                </details>

                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                        <span class="text-sm font-semibold text-blue-900">3 · Output</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <div class="grid gap-4 px-5 pb-4 text-sm text-gray-700 sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-[11px] font-semibold uppercase text-gray-400">Rencana</p>
                            <p class="whitespace-pre-wrap leading-relaxed">{{ $t->output_rencana ?: 'Belum ditentukan.' }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-[11px] font-semibold uppercase text-gray-400">Realisasi</p>
                            <p class="whitespace-pre-wrap leading-relaxed">{{ $t->output_realisasi ?: 'Belum tersedia.' }}</p>
                            @if ($t->file_output && $bolehBuka)
                                <a href="{{ route('pekerjaan.berkas', [$t, 'output']) }}" class="mt-2 inline-flex text-xs font-medium text-blue-700 underline">Unduh output</a>
                            @endif
                        </div>
                    </div>
                </details>
            </div>
        @endif
    </section>

    {{-- 3. Laporan kegiatan --}}
    <section class="animate-in delay-3 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 bg-gray-50/60 px-5 py-3">
            <h2 class="{{ $judulBagian }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Laporan kegiatan
            </h2>
        </div>

        <div class="divide-y divide-gray-100">
            <div class="p-5">
                <p class="mb-2 text-xs font-medium text-gray-500">Uraian kegiatan</p>
                <p class="text-sm leading-relaxed text-gray-900">{!! nl2br(e($laporan->uraian)) !!}</p>
            </div>

            <div class="p-5">
                <p class="mb-2 text-xs font-medium text-gray-500">Output / hasil</p>
                <div class="rounded-lg border p-4 {{ $outputGaya['box'] }}">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 {{ $outputGaya['judul'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $outputGaya['ikon'] }}" /></svg>
                        <p class="text-sm font-semibold {{ $outputGaya['judul'] }}">{{ ucfirst($outputGaya['label']) }}</p>
                    </div>
                    @if ($laporan->alasan)
                        <div class="mt-3 border-t border-black/5 pt-3">
                            <p class="mb-1 text-xs font-medium {{ $outputGaya['judul'] }}">Alasan {{ $laporan->output }}</p>
                            <p class="text-sm leading-relaxed {{ $outputGaya['isi'] }}">{!! nl2br(e($laporan->alasan)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-5">
                <p class="mb-2 text-xs font-medium text-gray-500">Lampiran bukti</p>
                @if ($laporan->file_lampiran)
                    <a href="{{ $laporan->file_lampiran }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#1F3864]/5 px-3 py-2 text-sm font-medium text-[#1F3864] transition-colors hover:bg-[#1F3864]/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        Buka lampiran
                    </a>
                @else
                    <p class="text-sm text-gray-400">Tidak ada lampiran.</p>
                @endif
            </div>
        </div>
    </section>
</div>