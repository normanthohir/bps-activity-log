{{-- Berlaku untuk laporan/show.blade.php, approval/show-kepala-bps.blade.php, dan approval/show.blade.php. --}}
@php
    $t = $laporan->tugas;
    $bolehBuka = $t && \App\Support\AksesPekerjaan::dapatLihat(auth()->user(), $t);

    if ($t) {
        $badgeTugas = match ($t->status) {
            'selesai' => ['bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500', 'Selesai'],
            'dikerjakan' => ['bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500', 'Dikerjakan'],
            default => ['bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400', 'Belum dikerjakan'],
        };
        $tenggatLewat = $t->tenggat && $t->status !== 'selesai' && $t->tenggat->isPast() && !$t->tenggat->isToday();
    }
@endphp

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4">
    <div class="flex items-center justify-between gap-2 mb-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Terkait pekerjaan</p>
        @if ($bolehBuka)
            <a href="{{ route('pekerjaan.show', $t) }}"
                class="group inline-flex items-center gap-1 text-xs font-semibold text-[#1F3864]">
                Buka pekerjaan
                <span class="transition-transform group-hover:translate-x-1 motion-reduce:transition-none">→</span>
            </a>
        @endif
    </div>

    @if (!$t)
        <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-600">
            Laporan mandiri, tidak terkait pekerjaan dari atasan.
        </div>
    @else
        {{-- Ringkasan pekerjaan --}}
        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <p class="text-sm font-semibold text-blue-900">{{ $t->judul }}</p>
                <span
                    class="inline-flex items-center gap-1.5 whitespace-nowrap {{ $badgeTugas[0] }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeTugas[1] }}"></span>{{ $badgeTugas[2] }}
                </span>
            </div>

            <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 text-xs sm:grid-cols-2">
                <div class="flex gap-2">
                    <dt class="w-20 shrink-0 text-gray-500">Tim</dt>
                    <dd class="font-medium text-gray-800">{{ $t->bagian?->nama_bagian ?? '-' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-20 shrink-0 text-gray-500">Pelaksana</dt>
                    <dd class="font-medium text-gray-800">{{ $t->penerimaTugas?->name ?? '-' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-20 shrink-0 text-gray-500">Diberikan</dt>
                    <dd class="font-medium text-gray-800">
                        {{ $t->pemberiTugas?->name ?? '-' }}
                        @if ($t->pemberiTugas)
                            <span
                                class="font-normal text-gray-500">({{ $t->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }})</span>
                        @endif
                    </dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-20 shrink-0 text-gray-500">Periode</dt>
                    <dd class="font-medium tabular-nums text-gray-800">
                        {{ $t->tanggal_mulai?->format('d/m/Y') ?? '-' }} &ndash;
                        <span
                            class="{{ $tenggatLewat ? 'text-red-600' : '' }}">{{ $t->tenggat?->format('d/m/Y') ?? '-' }}</span>
                        @if ($tenggatLewat)
                            <span class="font-normal text-red-500">(lewat tenggat)</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Input, Proses, Output (bisa dibuka-tutup, tanpa JavaScript) --}}
        <div class="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">

            <details class="group">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                    <span class="text-sm">
                        <span class="font-semibold text-blue-900">1 · Input</span>
                        @if ($t->jenis_input)
                            <span class="ml-1 text-[11px] uppercase text-gray-400">{{ $t->jenis_input }}</span>
                        @endif
                    </span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </summary>
                <div class="space-y-2 px-4 pb-4 text-sm text-gray-700">
                    @if ($t->jenis_input === 'surat')
                        <p class="text-xs text-gray-500">
                            {{ $t->asal_surat }} &middot; {{ $t->nomor_surat }} &middot;
                            {{ $t->tanggal_surat?->format('d/m/Y') }}
                        </p>
                    @endif
                    <p class="whitespace-pre-wrap leading-relaxed">{{ $t->uraian_input ?: 'Belum ada uraian input.' }}
                    </p>
                    @if ($t->file_input && $bolehBuka)
                        <a href="{{ route('pekerjaan.berkas', [$t, 'input']) }}"
                            class="inline-flex text-xs font-medium text-blue-700 underline">Unduh lampiran input</a>
                    @endif
                </div>
            </details>

            <details class="group">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                    <span class="text-sm font-semibold text-blue-900">2 · Proses</span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </summary>
                <div class="px-4 pb-4 text-sm text-gray-700">
                    <p class="whitespace-pre-wrap leading-relaxed">{{ $t->deskripsi ?: 'Tidak ada rincian proses.' }}
                    </p>
                </div>
            </details>

            <details class="group" open>
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                    <span class="text-sm font-semibold text-blue-900">3 · Output</span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180 motion-reduce:transition-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </summary>
                <div class="grid gap-3 px-4 pb-4 text-sm text-gray-700 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-[11px] font-semibold uppercase text-gray-400">Rencana</p>
                        <p class="whitespace-pre-wrap leading-relaxed">{{ $t->output_rencana ?: 'Belum ditentukan.' }}
                        </p>
                    </div>
                    <div>
                        <p class="mb-1 text-[11px] font-semibold uppercase text-gray-400">Realisasi</p>
                        <p class="whitespace-pre-wrap leading-relaxed">{{ $t->output_realisasi ?: 'Belum tersedia.' }}
                        </p>
                        @if ($t->file_output && $bolehBuka)
                            <a href="{{ route('pekerjaan.berkas', [$t, 'output']) }}"
                                class="mt-2 inline-flex text-xs font-medium text-blue-700 underline">Unduh output</a>
                        @endif
                    </div>
                </div>
            </details>
        </div>
    @endif
</div>
