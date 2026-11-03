@php
    $edit = isset($laporan);
    $tugasTerpilih = $tugasTerpilih ?? null;
    $dikembalikan = $edit && $laporan->status === 'dikembalikan';
    $logTerakhir = $edit ? $laporan->logApproval->last() : null;

    // Tanggal lampau tidak boleh dipilih. Saat edit, tanggal asli laporan tetap boleh dipertahankan.
    $tanggalAsli = $edit ? $laporan->tanggal->toDateString() : null;
    $tanggalMin = $edit ? min(now()->toDateString(), $tanggalAsli) : now()->toDateString();

    $judul = !$edit ? 'Buat Laporan' : ($dikembalikan ? 'Revisi Laporan' : 'Edit Laporan');

    $outputAwal = old(
        'output',
        $edit && in_array($laporan->output, ['selesai', 'tertunda', 'gagal'], true) ? $laporan->output : '',
    );

    $outputOpsi = [
        'selesai' => [
            'Selesai',
            'Tuntas',
            'peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700',
        ],
        'tertunda' => [
            'Tertunda',
            'Belum rampung',
            'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700',
        ],
        'gagal' => [
            'Gagal',
            'Tidak berhasil',
            'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700',
        ],
    ];
    $lokasiOpsi = ['kantor' => 'Kantor', 'lapangan' => 'Lapangan', 'dinas_luar' => 'Dinas Luar'];

    $kartuPilihan =
        'flex flex-col rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 active:scale-95 peer-focus-visible:ring-2 peer-focus-visible:ring-[#1F3864]/40 motion-reduce:transition-none';
    $inputKelas = 'w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]';
    $labelKelas = 'mb-1.5 block text-sm font-medium text-gray-700';
@endphp

{{-- Banner --}}
<div class="animate-in relative mb-5 overflow-hidden rounded-2xl bg-[#1F3864] p-5 text-white sm:p-6"
    style="background-image: radial-gradient(120% 90% at 100% 0%, rgb(53 91 145 / .7), transparent 60%)">
    {{-- <a href="{{ $routeIndex }}"
        class="group mb-3 inline-flex items-center gap-1.5 text-xs text-white/70 transition-colors hover:text-white">
        <svg class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5 motion-reduce:transition-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali ke daftar laporan
    </a> --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $judul }}</h1>
            <p class="mt-1 text-sm text-white/70">
                {{ auth()->user()->name }} &middot; {{ auth()->user()->bagian?->nama_bagian ?? '-' }}
            </p>
        </div>
        @if ($edit)
            <x-status-badge :status="$laporan->status" />
        @endif
    </div>
</div>

{{-- Pemberitahuan --}}
@if ($tugasTerpilih)
    <div
        class="animate-in delay-1 mb-5 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
        </svg>
        <p>Laporan ini dikaitkan dengan pekerjaan <span class="font-semibold">{{ $tugasTerpilih->judul }}</span>.</p>
    </div>
@endif

@if ($dikembalikan && $logTerakhir)
    <div class="animate-in delay-1 mb-5 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-red-800">Catatan dari atasan</p>
            <p class="mt-1 text-sm text-red-700">{{ $logTerakhir->catatan ?? 'Tidak ada catatan.' }}</p>
            <p class="mt-2 text-xs text-red-500">
                {{ $logTerakhir->approver->name ?? '-' }} &middot; {{ $logTerakhir->created_at->format('d M Y, H:i') }}
            </p>
        </div>
    </div>
@endif

{{-- Form --}}
<div class="animate-in delay-2 overflow-hidden rounded-2xl border border-gray-200 bg-white" x-data="{
    loading: false,
    output: @js($outputAwal),
    uraian: @js(old('uraian', $edit ? $laporan->uraian : '')),
    submit(aksi) {
        const form = this.$refs.form;
        form.querySelector('[name=aksi]').value = aksi;
        if (!form.reportValidity()) return;
        this.loading = true;
        form.submit();
    }
}">

    <form method="POST" action="{{ $action }}" x-ref="form" class="divide-y divide-gray-100">
        @csrf
        @if ($edit)
            @method('PUT')
        @endif
        <input type="hidden" name="aksi" value="draft">

        {{-- Kegiatan --}}
        <section class="space-y-4 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kegiatan</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal" class="{{ $labelKelas }}">Tanggal <span
                            class="text-red-500">*</span></label>
                    <input id="tanggal" type="date" name="tanggal" required min="{{ $tanggalMin }}"
                        value="{{ old('tanggal', $edit ? $tanggalAsli : now()->toDateString()) }}"
                        class="{{ $inputKelas }} @error('tanggal') border-red-500 @else border-gray-300 @enderror">
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $edit ? 'Tanggal lampau hanya boleh tanggal asli laporan ini.' : 'Tidak bisa memilih tanggal yang sudah lewat.' }}
                    </p>
                    @error('tanggal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tugas_id" class="{{ $labelKelas }}">Terkait Pekerjaan</label>
                    <select id="tugas_id" name="tugas_id"
                        class="{{ $inputKelas }} @error('tugas_id') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Tidak terkait pekerjaan</option>
                        @foreach ($tugasAktif as $tugas)
                            <option value="{{ $tugas->id }}" @selected(old('tugas_id', $edit ? $laporan->tugas_id : $tugasTerpilih?->id) == $tugas->id)>
                                {{ $tugas->judul }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Hanya pekerjaan selesai yang belum pernah dilaporkan.</p>
                    @error('tugas_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="uraian" class="text-sm font-medium text-gray-700">Uraian Kegiatan <span
                            class="text-red-500">*</span></label>
                    <span class="text-xs tabular-nums text-gray-400" x-text="uraian.length + ' karakter'"></span>
                </div>
                <textarea id="uraian" name="uraian" rows="4" required x-model="uraian"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                    class="{{ $inputKelas }} @error('uraian') border-red-500 @else border-gray-300 @enderror">{{ old('uraian', $edit ? $laporan->uraian : '') }}</textarea>
                @error('uraian')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Hasil --}}
        <section class="space-y-5 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hasil</h2>

            <fieldset>
                <legend class="{{ $labelKelas }}">Lokasi <span class="text-red-500">*</span></legend>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($lokasiOpsi as $nilai => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="lokasi" value="{{ $nilai }}" required
                                class="peer sr-only" @checked(old('lokasi', $edit ? $laporan->lokasi : '') === $nilai)>
                            <span
                                class="{{ $kartuPilihan }} items-center text-center peer-checked:border-[#1F3864] peer-checked:bg-[#1F3864]/5 peer-checked:text-[#1F3864]">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('lokasi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset>
                <legend class="{{ $labelKelas }}">Output / Hasil <span class="text-red-500">*</span></legend>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($outputOpsi as $nilai => [$label, $keterangan, $kelasAktif])
                        <label class="cursor-pointer">
                            <input type="radio" name="output" value="{{ $nilai }}" x-model="output" required
                                class="peer sr-only" @checked($outputAwal === $nilai)>
                            <span class="{{ $kartuPilihan }} {{ $kelasAktif }}">
                                <span>{{ $label }}</span>
                                <span
                                    class="hidden text-[11px] font-normal opacity-70 sm:block">{{ $keterangan }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('output')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- Alasan: hanya jika gagal atau tertunda --}}
            <div x-show="output === 'gagal' || output === 'tertunda'" x-cloak x-transition>
                <label for="alasan" class="{{ $labelKelas }}">
                    <span x-text="output === 'gagal' ? 'Alasan Gagal' : 'Alasan Tertunda'"></span>
                    <span class="text-red-500">*</span>
                </label>
                <textarea id="alasan" name="alasan" rows="2" placeholder="Jelaskan secara detail kendalanya..."
                    :required="output === 'gagal' || output === 'tertunda'"
                    class="{{ $inputKelas }} @error('alasan') border-red-500 @else border-gray-300 @enderror">{{ old('alasan', $edit ? $laporan->alasan : '') }}</textarea>
                @error('alasan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Bukti --}}
        <section class="p-5 sm:p-6">
            <h2 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Bukti</h2>
            <label for="file_lampiran" class="{{ $labelKelas }}">Link Lampiran (Opsional)</label>
            <input id="file_lampiran" type="url" name="file_lampiran"
                value="{{ old('file_lampiran', $edit ? $laporan->file_lampiran : '') }}"
                placeholder="https://drive.google.com/file/d/..."
                class="{{ $inputKelas }} @error('file_lampiran') border-red-500 @else border-gray-300 @enderror">
            <p class="mt-1 text-xs text-gray-400">Paste link Google Drive bukti kegiatan.</p>
            @error('file_lampiran')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- Tombol aksi --}}
        <div class="flex flex-col-reverse gap-2 bg-gray-50/60 p-5 sm:flex-row sm:justify-end sm:p-6">
            <a href="{{ $routeIndex }}"
                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                Batal
            </a>

            <x-submit-button type="submit" onclick="submit('ajukan')">
                {{ $dikembalikan ? 'Kirim Ulang Laporan' : 'Kirim Laporan' }}
            </x-submit-button>
        </div>
    </form>
</div>
