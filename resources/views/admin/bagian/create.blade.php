{{-- resources/views/admin/bagian/create.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-5">Tambah Bagian/Seksi</p>

            <form method="POST" action="{{ route('admin.bagian.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Nama bagian</label>
                    <input type="text" name="nama_bagian" value="{{ old('nama_bagian') }}"
                           placeholder="contoh: Seksi Statistik Distribusi"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('nama_bagian') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Kode bagian</label>
                    <input type="text" name="kode_bagian" value="{{ old('kode_bagian') }}"
                           placeholder="contoh: STAT-DIST"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('kode_bagian') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <p class="text-xs text-gray-400 mb-4">
                    Kepala bagian diatur belakangan lewat menu Edit, setelah akun kepala bagiannya dibuat.
                </p>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">Simpan</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
