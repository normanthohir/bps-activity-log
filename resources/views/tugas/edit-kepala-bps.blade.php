{{-- resources/views/tugas/edit-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-1">Edit Tugas</p>
            <p class="text-sm text-gray-500 mb-5">
                Ditugaskan ke {{ $tugas->penerimaTugas->name }} — {{ $tugas->bagian->nama_bagian }}
                <br><span class="text-xs">(penerima tugas tidak bisa diubah — hapus dan buat ulang jika perlu ganti penerima)</span>
            </p>

            <form method="POST" action="{{ route('kepala-bps.tugas.update', $tugas) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Judul tugas</label>
                    <input type="text" name="judul" value="{{ old('judul', $tugas->judul) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('judul') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Tenggat</label>
                    <input type="date" name="tenggat" value="{{ old('tenggat', $tugas->tenggat?->toDateString()) }}"
                           class="w-full rounded-lg border-gray-300">
                    @error('tenggat') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>