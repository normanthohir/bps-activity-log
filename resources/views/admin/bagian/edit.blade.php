{{-- resources/views/admin/bagian/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-5">Edit Bagian: {{ $bagian->nama_bagian }}</p>

            <form method="POST" action="{{ route('admin.bagian.update', $bagian) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Nama bagian</label>
                    <input type="text" name="nama_bagian" value="{{ old('nama_bagian', $bagian->nama_bagian) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('nama_bagian') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Kode bagian</label>
                    <input type="text" name="kode_bagian" value="{{ old('kode_bagian', $bagian->kode_bagian) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('kode_bagian') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Kepala bagian</label>
                    <select name="kepala_bagian_id" class="w-full rounded-lg border-gray-300">
                        <option value="">- Belum ditentukan -</option>
                        @foreach ($calonKepala as $pegawai)
                            <option value="{{ $pegawai->id }}" @selected(old('kepala_bagian_id', $bagian->kepala_bagian_id) == $pegawai->id)>
                                {{ $pegawai->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Hanya menampilkan pegawai yang sudah terdaftar di bagian ini.
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
