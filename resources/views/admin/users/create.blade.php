{{-- resources/views/admin/users/create.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Tambah Akun Pegawai</h1>
            <p class="text-sm text-gray-500 mt-0.5">Buat akun baru untuk pegawai sistem.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 errors: {},
                 name: '{{ old('name') }}',
                 nip: '{{ old('nip') }}',
                 email: '{{ old('email') }}',
                 role: '{{ old('role', 'staf') }}',
                 bagian_id: '{{ old('bagian_id') }}',
                 validate() {
                     this.errors = {};
                     if (!this.name.trim()) this.errors.name = 'Nama wajib diisi.';
                     if (this.name.length > 255) this.errors.name = 'Nama maksimal 255 karakter.';
                     if (!this.nip.trim()) this.errors.nip = 'NIP wajib diisi.';
                     if (this.nip.length > 20) this.errors.nip = 'NIP maksimal 20 karakter.';
                     if (!this.email.trim()) this.errors.email = 'Email wajib diisi.';
                     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) this.errors.email = 'Format email tidak valid.';
                     if (!this.role) this.errors.role = 'Role wajib dipilih.';
                     if (['staf', 'kepala_bagian'].includes(this.role) && !this.bagian_id) {
                         this.errors.bagian_id = 'Bagian wajib dipilih untuk role ini.';
                     }
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-6" x-ref="form" novalidate>
                @csrf

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="name"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.name ? 'border-red-500' : 'border-gray-300'"
                           placeholder="Masukkan nama lengkap">
                    <template x-if="errors.name">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.name"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" x-model="nip"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.nip ? 'border-red-500' : 'border-gray-300'"
                           placeholder="Nomor Induk Pegawai">
                    <template x-if="errors.nip">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.nip"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" x-model="email"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.email ? 'border-red-500' : 'border-gray-300'"
                           placeholder="contoh: nama@bps.go.id">
                    <template x-if="errors.email">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.email"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" x-model="role"
                            class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                            :class="errors.role ? 'border-red-500' : 'border-gray-300'">
                        <option value="staf">Staf</option>
                        <option value="kepala_bagian">Kepala Bagian</option>
                        <option value="kepala_bps">Kepala BPS Kota</option>
                        <option value="admin">Admin</option>
                    </select>
                    <template x-if="errors.role">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.role"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Bagian <span x-show="['staf', 'kepala_bagian'].includes(role)" class="text-red-500">*</span></label>
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

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Atasan Langsung</label>
                    <select name="atasan_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Tidak ada -</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}">{{ $atasan->name }} ({{ ucwords(str_replace('_',' ',$atasan->role)) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs px-4 py-2.5 rounded-lg mb-5">
                    Password default untuk akun baru: <strong>password</strong>
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="button" @click="submit()"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
