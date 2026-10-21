{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

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
                 bagian_id: '{{ old('bagian_id', $user->bagian_id) }}',
                 validate() {
                     this.errors = {};
                     if (!this.name.trim()) this.errors.name = 'Nama wajib diisi.';
                     if (this.name.length > 255) this.errors.name = 'Nama maksimal 255 karakter.';
                     if (!this.nip.trim()) this.errors.nip = 'NIP wajib diisi.';
                     if (this.nip.length > 20) this.errors.nip = 'NIP maksimal 20 karakter.';
                     if (!this.email.trim()) this.errors.email = 'Email wajib diisi.';
                     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) this.errors.email = 'Format email tidak valid.';
                     // Tim utama opsional: ketua/anggota dapat menjadi anggota banyak tim.
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6" x-ref="form" novalidate>
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

                {{-- Jabatan ketua/anggota bukan jenis akun tetap. Hak khusus hanya untuk pimpinan/admin. --}}
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

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Tim utama (opsional)</label>
                    <select name="bagian_id" x-model="bagian_id"
                            class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                            :class="errors.bagian_id ? 'border-red-500' : 'border-gray-300'">
                        <option value="">- Tidak ada (untuk Kepala BPS/Admin) -</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}">{{ $bagian->nama_bagian }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.bagian_id">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.bagian_id"></p>
                    </template>
                </div>

                <div class="mb-5 rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Sebagai anggota tim (bisa banyak)</label>
                    <p class="text-xs text-gray-600 mb-3">Centang semua tim tempat pegawai menjadi anggota. Tim utama otomatis terdaftar. Tim yang dipimpin tetap dapat dicentang juga.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($daftarBagian as $tim)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="bagian_ids[]" value="{{ $tim->id }}"
                                @checked(in_array($tim->id, old('bagian_ids', $user->tim->pluck('id')->all()))) class="rounded border-slate-300">{{ $tim->nama_bagian }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-5 rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Sebagai Ketua Tim (bisa banyak)</label>
                    <p class="text-xs text-gray-600 mb-3">Peran ini khusus untuk tim yang dicentang. Pegawai yang sama tetap bisa menjadi anggota biasa pada tim lain. Tim yang sudah memiliki ketua lain harus diubah melalui Edit Tim.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($daftarBagian as $tim)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ketua_tim_ids[]" value="{{ $tim->id }}"
                                @checked(in_array($tim->id, old('ketua_tim_ids', $ketuaTimIds))) class="rounded border-slate-300">{{ $tim->nama_bagian }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Atasan Langsung</label>
                    <select name="atasan_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Tidak ada -</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}" @selected(old('atasan_id', $user->atasan_id) == $atasan->id)>{{ $atasan->name }} ({{ ucwords(str_replace('_',' ',$atasan->role)) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="button" @click="submit()"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Reset Password --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Reset Password</h2>
                <p class="text-sm text-gray-500 mb-4">Reset password user ke default: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">password</code></p>
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <button type="submit" data-confirm="Reset password {{ $user->name }} ke default?"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
