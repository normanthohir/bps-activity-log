{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xxl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Akun: {{ $user->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi akun pegawai.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 errors: {},
                 name: '{{ old('name', $user->name) }}',
                 nip: '{{ old('nip', $user->nip) }}',
                 email: '{{ old('email', $user->email) }}',
                 role: '{{ old('role', in_array($user->role, ['admin','kepala_bps']) ? $user->role : 'staf') }}',
                 validate() {
                     this.errors = {};
                     if (!this.name.trim()) this.errors.name = 'Nama wajib diisi.';
                     if (this.name.length > 255) this.errors.name = 'Nama maksimal 255 karakter.';
                     if (!this.nip.trim()) this.errors.nip = 'NIP wajib diisi.';
                     if (this.nip.length > 20) this.errors.nip = 'NIP maksimal 20 karakter.';
                     if (!this.email.trim()) this.errors.email = 'Email wajib diisi.';
                     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) this.errors.email = 'Format email tidak valid.';
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6" x-ref="form" @submit.prevent="submit" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="name"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.name ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.name">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.name"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" x-model="nip"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.nip ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.nip">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.nip"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" x-model="email"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.email ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.email">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.email"></p>
                    </template>
                </div>

                {{-- Hak akses khusus --}}
                <details class="mb-4 rounded-lg border border-slate-200 px-4 py-3 bg-slate-50">
                    <summary class="cursor-pointer text-sm font-medium text-slate-700">Hak sistem khusus (opsional)</summary>
                    <p class="text-xs text-slate-500 mt-2 mb-2">Akun pegawai biasa tidak perlu memilih jenis akun. Hak sebagai anggota atau ketua tim mengikuti centang keanggotaan di bawah dan dapat dipilih setelah login.</p>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Akses sistem</label>
                    <select name="role" x-model="role" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="staf">Pegawai biasa (standar)</option>
                        <option value="kepala_bps">Kepala BPS Kota</option>
                        <option value="admin">Admin sistem</option>
                    </select>
                    @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </details>

                {{-- Anggota Tim --}}
                <div class="mb-5 rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Sebagai anggota tim (bisa banyak)</label>
                    <p class="text-xs text-gray-600 mb-3">Centang semua tim tempat pegawai menjadi anggota. Tim yang dipimpin tetap dapat dicentang juga.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($daftarBagian as $tim)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="bagian_ids[]" value="{{ $tim->id }}"
                                @checked(in_array($tim->id, old('bagian_ids', $user->tim->pluck('id')->all()))) class="rounded border-slate-300">
                                {{ $tim->nama_bagian }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Ketua Tim --}}
                <div class="mb-5 rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Sebagai Ketua Tim (bisa banyak)</label>
                    <p class="text-xs text-gray-600 mb-3">Peran ini khusus untuk tim yang dicentang. Pegawai yang sama tetap bisa menjadi anggota biasa pada tim lain.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($daftarBagian as $tim)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="ketua_tim_ids[]" value="{{ $tim->id }}"
                                @checked(in_array($tim->id, old('ketua_tim_ids', $ketuaTimIds))) class="rounded border-slate-300">
                                {{ $tim->nama_bagian }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Atasan Langsung --}}
                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Atasan Langsung</label>
                    <select name="atasan_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Tidak ada -</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}" @selected(old('atasan_id', $user->atasan_id) == $atasan->id)>
                                {{ $atasan->name }} ({{ ucwords(str_replace('_',' ',$atasan->role)) }})
                            </option>
                        @endforeach
                    </select>
                    @error('atasan_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#1F3864] rounded-lg hover:bg-[#1F3864]/90">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
