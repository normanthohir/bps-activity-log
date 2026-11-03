{{-- resources/views/admin/users/partials/form-akun.blade.php --}}
{{-- Dipakai bersama oleh admin/users/create dan admin/users/edit.
     Wajib: $action, $judul, $deskripsi, $tombol, $daftarBagian, $calonAtasan.
     Opsional: $akun (mode edit), $timSaatIni. --}}
@php
    $akun = $akun ?? null;
    $edit = $akun !== null;

    $input = 'w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]';
    $label = 'mb-1.5 block text-sm font-medium text-gray-700';
    $warnaPeran = [
        '' => 'peer-checked:bg-white peer-checked:text-gray-700 peer-checked:shadow-sm',
        'anggota' => 'peer-checked:bg-[#1F3864] peer-checked:text-white peer-checked:shadow-sm',
        'ketua' => 'peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-sm',
    ];

    $roleAwal = old('role', $edit && in_array($akun->role, ['admin', 'kepala_bps']) ? $akun->role : 'staf');
    $timAwal = old('tim', $timSaatIni ?? []);
@endphp

<a href="{{ route('admin.users.index') }}"
    class="animate-in group mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition-colors hover:text-[#1F3864]">
    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5 motion-reduce:transition-none" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Kembali ke daftar akun
</a>

<div class="animate-in mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">{{ $judul }}</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $deskripsi }}</p>
    </div>
    @if ($edit)
        @if ($akun->hasVerifiedEmail())
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Email terverifikasi
            </span>
        @else
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Email belum diverifikasi
            </span>
        @endif
    @endif
</div>

<div class="animate-in delay-1 overflow-hidden rounded-2xl border border-gray-200 bg-white" x-data="{
    loading: false,
    role: @js($roleAwal),
    bagian_id: @js((string) old('bagian_id', $akun?->bagian_id)),
    tim: Object.assign(
        Object.fromEntries(@js($daftarBagian->pluck('id')->values()).map(id => [id, ''])),
        @js((object) $timAwal)
    ),
}">

    <form method="POST" action="{{ $action }}" @submit="loading = true" class="divide-y divide-gray-100">
        @csrf
        @if ($edit)
            @method('PUT')
        @endif

        {{-- Identitas --}}
        <section class="space-y-4 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Identitas</h2>

            <div>
                <label for="name" class="{{ $label }}">Nama Lengkap <span
                        class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $akun?->name) }}" required
                    maxlength="255" placeholder="Masukkan nama lengkap"
                    class="{{ $input }} @error('name') border-red-500 @else border-gray-300 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="nip" class="{{ $label }}">NIP <span class="text-red-500">*</span></label>
                    <input id="nip" type="text" name="nip" value="{{ old('nip', $akun?->nip) }}" required
                        maxlength="20" placeholder="Nomor Induk Pegawai"
                        class="{{ $input }} @error('nip') border-red-500 @else border-gray-300 @enderror">
                    @error('nip')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="{{ $label }}">Email <span class="text-red-500">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $akun?->email) }}"
                        required placeholder="nama@bps.go.id"
                        class="{{ $input }} @error('email') border-red-500 @else border-gray-300 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Akses --}}
        <section class="space-y-4 p-5 sm:p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Akses</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="role" class="{{ $label }}">Akses sistem <span
                            class="text-red-500">*</span></label>
                    <select id="role" name="role" x-model="role" required
                        class="{{ $input }} @error('role') border-red-500 @else border-gray-300 @enderror">
                        <option value="staf">Pegawai biasa</option>
                        <option value="kepala_bps">Kepala BPS Kota</option>
                        <option value="admin">Admin sistem</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Ketua Tim ditentukan lewat pilihan tim di bawah.</p>
                    @error('role')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="atasan_id" class="{{ $label }}">Atasan Langsung</label>
                    <select id="atasan_id" name="atasan_id"
                        class="{{ $input }} @error('atasan_id') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Tidak ada</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}" @selected(old('atasan_id', $akun?->atasan_id) == $atasan->id)>
                                {{ $atasan->name }} ({{ ucwords(str_replace('_', ' ', $atasan->role)) }})
                            </option>
                        @endforeach
                    </select>
                    @error('atasan_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Keanggotaan tim: hanya untuk pegawai biasa --}}
        <section x-show="role === 'staf'" x-transition x-cloak class="p-5 sm:p-6">
            {{-- fieldset disabled: isinya tidak ikut terkirim untuk Kepala BPS/Admin --}}
            <fieldset :disabled="role !== 'staf'" class="space-y-4">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Keanggotaan Tim</h2>

                <div>
                    <label for="bagian_id" class="{{ $label }}">Tim utama <span
                            class="text-red-500">*</span></label>
                    <select id="bagian_id" name="bagian_id" x-model="bagian_id" :required="role === 'staf'"
                        @change="if (bagian_id && !tim[bagian_id]) tim[bagian_id] = 'anggota'"
                        class="{{ $input }} @error('bagian_id') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Pilih tim utama</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}" @selected(old('bagian_id', $akun?->bagian_id) == $bagian->id)>
                                {{ $bagian->nama_bagian }}
                            </option>
                        @endforeach
                    </select>
                    @error('bagian_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="{{ $label }}">Peran di setiap tim</p>
                    <p class="mb-3 text-xs text-gray-500">
                        Pegawai bisa menjadi anggota di beberapa tim dan ketua di tim lain. Tim utama otomatis menjadi
                        anggota. Tim yang sudah punya ketua lain tidak bisa dipilih sebagai Ketua.
                    </p>

                    @error('tim')
                        <p class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                    @error('tim.*')
                        <p class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p>
                    @enderror

                    <div class="space-y-2">
                        @foreach ($daftarBagian as $tim)
                            @php $dipimpinLain = $tim->kepala_bagian_id && (int) $tim->kepala_bagian_id !== (int) $akun?->id; @endphp
                            <div
                                class="flex flex-col gap-2 rounded-xl border border-gray-200 px-3 py-2.5 transition hover:border-gray-300 sm:flex-row sm:items-center sm:justify-between motion-reduce:transition-none">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-800">{{ $tim->nama_bagian }}</p>
                                    <p x-show="bagian_id == '{{ $tim->id }}'" x-cloak
                                        class="text-[11px] font-medium text-[#1F3864]">Tim utama</p>
                                    @if ($dipimpinLain)
                                        <p class="text-[11px] text-gray-400">Sudah memiliki ketua lain</p>
                                    @endif
                                </div>

                                <div
                                    class="inline-flex shrink-0 self-start rounded-lg bg-gray-100 p-0.5 text-xs font-medium sm:self-auto">
                                    @foreach (['' => 'Bukan', 'anggota' => 'Anggota', 'ketua' => 'Ketua'] as $nilai => $teks)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="tim[{{ $tim->id }}]"
                                                value="{{ $nilai }}" x-model="tim[{{ $tim->id }}]"
                                                class="peer sr-only" @if ($nilai === 'ketua' && $dipimpinLain) disabled @endif
                                                @if ($nilai === '') :disabled="bagian_id == '{{ $tim->id }}'" @endif>
                                            <span
                                                class="block rounded-md px-3 py-1.5 text-gray-500 transition active:scale-95 peer-focus-visible:ring-2 peer-focus-visible:ring-[#1F3864]/40 peer-disabled:cursor-not-allowed peer-disabled:opacity-40 motion-reduce:transition-none {{ $warnaPeran[$nilai] }}">
                                                {{ $teks }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </fieldset>
        </section>

        {{-- Aksi --}}
        <div class="space-y-4 bg-gray-50/60 p-5 sm:p-6">
            @if (!$edit)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                    Password default untuk akun baru: <strong>password</strong>. Pegawai wajib memverifikasi email lebih
                    dulu.
                </div>
            @else
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs text-gray-500">
                   Mengubah akses menjadi Kepala BPS atau Admin akan melepas
                    keanggotaan dan jabatan ketua tim akun ini.
                </div>
            @endif

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                    Batal
                </a>
                <x-submit-button type="submit">
                    {{ $tombol }}
                </x-submit-button>
            </div>
        </div>
    </form>
</div>
