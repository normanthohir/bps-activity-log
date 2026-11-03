@php
    $bagian = $bagian ?? null;
    $edit = $bagian !== null;

    $input = 'w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]';
    $label = 'mb-1.5 block text-sm font-medium text-gray-700';

    $dipilihAwal = collect(old('anggota_ids', $anggotaTerpilih ?? []))->map(fn($id) => (string) $id)->values();
    $semuaId = $calonAnggota->pluck('id')->map(fn($id) => (string) $id)->values();
@endphp

<a href="{{ route('admin.bagian.index') }}"
    class="animate-in group mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition-colors hover:text-[#1F3864]">
    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5 motion-reduce:transition-none" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Kembali ke daftar tim
</a>

<div class="animate-in mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">{{ $judul }}</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $deskripsi }}</p>
    </div>
    @if ($edit)
        <span
            class="inline-flex items-center gap-1.5 rounded-full bg-[#DCE6F1] px-2.5 py-1 text-xs font-semibold text-[#1F3864]">
            {{ $bagian->kode_bagian }}
        </span>
    @endif
</div>

<div class="animate-in delay-1 overflow-hidden rounded-2xl border border-gray-200 bg-white" x-data="{
    loading: false,
    ketua: @js((string) old('kepala_bagian_id', $bagian?->kepala_bagian_id)),
    dipilih: @js($dipilihAwal),
    semua: @js($semuaId),
    cari: '',
    total() {
        const s = new Set(this.dipilih);
        if (this.ketua) s.add(this.ketua);
        return s.size;
    },
    pilihSemua() { this.dipilih = this.semua.filter(id => id !== this.ketua); },
    kosongkan() { this.dipilih = []; },
}">

    <form method="POST" action="{{ $action }}" @submit="loading = true" class="divide-y divide-gray-100">
        @csrf
        @if ($edit)
            @method('PUT')
        @endif

        {{-- Informasi tim --}}
        <section class="space-y-4 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Informasi Tim</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="nama_bagian" class="{{ $label }}">Nama Tim <span
                            class="text-red-500">*</span></label>
                    <input id="nama_bagian" type="text" name="nama_bagian"
                        value="{{ old('nama_bagian', $bagian?->nama_bagian) }}" required maxlength="255"
                        placeholder="contoh: Seksi Statistik Distribusi"
                        class="{{ $input }} @error('nama_bagian') border-red-500 @else border-gray-300 @enderror">
                    @error('nama_bagian')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode_bagian" class="{{ $label }}">Kode Tim <span
                            class="text-red-500">*</span></label>
                    <input id="kode_bagian" type="text" name="kode_bagian"
                        value="{{ old('kode_bagian', $bagian?->kode_bagian) }}" required maxlength="50"
                        placeholder="STAT-DIST"
                        class="{{ $input }} uppercase @error('kode_bagian') border-red-500 @else border-gray-300 @enderror">
                    @error('kode_bagian')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Ketua --}}
        <section class="space-y-3 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ketua Tim</h2>

            <div>
                <label for="kepala_bagian_id" class="{{ $label }}">Ketua tim (opsional)</label>
                <select id="kepala_bagian_id" name="kepala_bagian_id" x-model="ketua"
                    class="{{ $input }} @error('kepala_bagian_id') border-red-500 @else border-gray-300 @enderror">
                    <option value="">Belum ditentukan</option>
                    @foreach ($calonKetua as $pegawai)
                        <option value="{{ $pegawai->id }}">
                            {{ $pegawai->name }}
                            ({{ $pegawai->nip }})
                            {{ $pegawai->bagian ? ' · ' . $pegawai->bagian->nama_bagian : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">
                    Satu pegawai boleh memimpin beberapa tim. Ketua otomatis menjadi anggota tim ini dan berperan Kepala
                    Tim.
                </p>
                @error('kepala_bagian_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Anggota --}}
        <section class="space-y-3 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Anggota Tim</h2>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-[#DCE6F1] px-2.5 py-1 text-xs font-semibold tabular-nums text-[#1F3864]">
                    <span x-text="total()"></span> anggota
                </span>
            </div>

            <div class="flex items-start gap-2.5 rounded-lg bg-blue-50 px-3 py-2.5 text-xs text-blue-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <p>
                    {{ $edit
                        ? 'Daftar berisi anggota tim ini dan pegawai yang belum memiliki tim.'
                        : 'Hanya pegawai yang belum tergabung di tim mana pun yang bisa dipilih.' }}
                    Untuk memindahkan pegawai dari tim lain, ubah lewat Kelola Akun Pegawai.
                </p>
            </div>

            @error('anggota_ids')
                <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p>
            @enderror
            @error('anggota_ids.*')
                <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p>
            @enderror

            @if ($calonAnggota->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center">
                    <p class="text-sm font-medium text-gray-700">Belum ada pegawai yang bisa dipilih</p>
                    <p class="mt-1 text-xs text-gray-500">Semua pegawai sudah tergabung di tim. Tim tetap bisa disimpan
                        tanpa anggota.</p>
                </div>
            @else
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="group relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-[#1F3864]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                        </svg>
                        <input type="search" x-model="cari" placeholder="Cari nama atau NIP..."
                            class="{{ $input }} border-gray-300 pl-9">
                    </div>
                    <div class="flex gap-2 text-xs font-medium">
                        <button type="button" @click="pilihSemua()"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 transition hover:bg-gray-50 active:scale-95">Pilih
                            semua</button>
                        <button type="button" @click="kosongkan()"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 transition hover:bg-gray-50 active:scale-95">Kosongkan</button>
                    </div>
                </div>

                <div class="grid max-h-80 grid-cols-1 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                    @foreach ($calonAnggota as $pegawai)
                        <label class="cursor-pointer"
                            data-cari="{{ \Illuminate\Support\Str::lower($pegawai->name . ' ' . $pegawai->nip) }}"
                            x-show="!cari || $el.dataset.cari.includes(cari.toLowerCase())">
                            <input type="checkbox" name="anggota_ids[]" value="{{ $pegawai->id }}"
                                x-model="dipilih" :disabled="ketua === '{{ $pegawai->id }}'" class="peer sr-only">
                            <span
                                class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-2.5 transition hover:border-gray-300 peer-checked:border-[#1F3864] peer-checked:bg-[#1F3864]/5 peer-focus-visible:ring-2 peer-focus-visible:ring-[#1F3864]/40 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 motion-reduce:transition-none">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#DCE6F1] text-xs font-semibold text-[#1F3864]">
                                    {{ strtoupper(substr($pegawai->name, 0, 1)) }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-sm font-medium text-gray-800">{{ $pegawai->name }}</span>
                                    <span
                                        class="block truncate text-xs tabular-nums text-gray-400">{{ $pegawai->nip }}</span>
                                </span>
                                <span x-show="ketua === '{{ $pegawai->id }}'" x-cloak
                                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Ketua</span>
                                <svg x-show="dipilih.includes('{{ $pegawai->id }}') || ketua === '{{ $pegawai->id }}'"
                                    x-cloak class="h-5 w-5 shrink-0 text-[#1F3864]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Aksi --}}
        <div class="flex flex-col-reverse gap-2 bg-gray-50/60 p-5 sm:flex-row sm:justify-end sm:p-6">
            <a href="{{ route('admin.bagian.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                Batal
            </a>
            <x-submit-button type="submit">
                {{ $tombol }}
            </x-submit-button>
        </div>
    </form>
</div>F
