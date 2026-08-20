{{-- resources/views/admin/bagian/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Bagian: {{ $bagian->nama_bagian }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi bagian/seksi.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.bagian.update', $bagian) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Bagian <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_bagian" value="{{ old('nama_bagian', $bagian->nama_bagian) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('nama_bagian') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Kode Bagian <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_bagian" value="{{ old('kode_bagian', $bagian->kode_bagian) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('kode_bagian') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Kepala Bagian</label>
                    <select name="kepala_bagian_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Belum ditentukan -</option>
                        @foreach ($calonKepala as $pegawai)
                            <option value="{{ $pegawai->id }}" @selected(old('kepala_bagian_id', $bagian->kepala_bagian_id) == $pegawai->id)>{{ $pegawai->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hanya menampilkan pegawai yang sudah terdaftar di bagian ini.</p>
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.bagian.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
