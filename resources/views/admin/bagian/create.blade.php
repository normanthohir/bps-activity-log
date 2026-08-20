{{-- resources/views/admin/bagian/create.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Tambah Bagian/Seksi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Buat unit kerja baru dalam struktur organisasi.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 errors: {},
                 nama_bagian: '{{ old('nama_bagian') }}',
                 kode_bagian: '{{ old('kode_bagian') }}',
                 validate() {
                     this.errors = {};
                     if (!this.nama_bagian.trim()) this.errors.nama_bagian = 'Nama bagian wajib diisi.';
                     if (this.nama_bagian.length > 255) this.errors.nama_bagian = 'Nama bagian maksimal 255 karakter.';
                     if (!this.kode_bagian.trim()) this.errors.kode_bagian = 'Kode bagian wajib diisi.';
                     if (this.kode_bagian.length > 50) this.errors.kode_bagian = 'Kode bagian maksimal 50 karakter.';
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route('admin.bagian.store') }}" class="p-6" x-ref="form" novalidate>
                @csrf

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Bagian <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_bagian" x-model="nama_bagian"
                           placeholder="contoh: Seksi Statistik Distribusi"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.nama_bagian ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.nama_bagian">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.nama_bagian"></p>
                    </template>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Kode Bagian <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_bagian" x-model="kode_bagian"
                           placeholder="contoh: STAT-DIST"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.kode_bagian ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.kode_bagian">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.kode_bagian"></p>
                    </template>
                </div>

                <div class="bg-blue-50 border border-blue-200 text-blue-700 text-xs px-4 py-2.5 rounded-lg mb-5">
                    Kepala bagian diatur belakangan lewat menu Edit, setelah akun kepala bagiannya dibuat.
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.bagian.index') }}"
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
