{{-- resources/views/admin/bagian/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Tim: {{ $bagian->nama_bagian }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi tim.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 errors: {},
                 nama_bagian: '{{ old('nama_bagian', $bagian->nama_bagian) }}',
                 kode_bagian: '{{ old('kode_bagian', $bagian->kode_bagian) }}',
                 validate() {
                     this.errors = {};
                     if (!this.nama_bagian.trim()) this.errors.nama_bagian = 'Nama tim wajib diisi.';
                     if (this.nama_bagian.length > 255) this.errors.nama_bagian = 'Nama tim maksimal 255 karakter.';
                     if (!this.kode_bagian.trim()) this.errors.kode_bagian = 'Kode tim wajib diisi.';
                     if (this.kode_bagian.length > 50) this.errors.kode_bagian = 'Kode tim maksimal 50 karakter.';
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route('admin.bagian.update', $bagian) }}" class="p-6" x-ref="form" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Tim <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_bagian" x-model="nama_bagian"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.nama_bagian ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.nama_bagian">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.nama_bagian"></p>
                    </template>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Kode Tim <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_bagian" x-model="kode_bagian"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.kode_bagian ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.kode_bagian">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.kode_bagian"></p>
                    </template>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Ketua Tim</label>
                    <select name="kepala_bagian_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Belum ditentukan -</option>
                        @foreach ($calonKepala as $pegawai)
                            <option value="{{ $pegawai->id }}" @selected(old('kepala_bagian_id', $bagian->kepala_bagian_id) == $pegawai->id)>{{ $pegawai->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Menampilkan semua pegawai. Peran ketua ditentukan per tim, bukan oleh jenis akun.</p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Anggota Tim (bisa banyak)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-auto rounded-lg border p-3">
                        @foreach ($calonKepala as $pegawai)
                            <label class="text-sm flex items-center gap-2"><input type="checkbox" name="anggota_ids[]" value="{{ $pegawai->id }}"
                                @checked(in_array($pegawai->id, old('anggota_ids', $bagian->anggotaTim->pluck('id')->all()))) class="rounded border-slate-300">{{ $pegawai->name }}</label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Ketua otomatis menjadi anggota. Melepas centang anggota di sini tidak menghapusnya dari tim lain.</p>
                </div>
                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.bagian.index') }}"
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

    </div>
</x-app-layout>
