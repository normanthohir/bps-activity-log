{{-- resources/views/tugas/create.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-1">Beri Tugas ke Staf</p>
            <p class="text-sm text-gray-500 mb-5">{{ auth()->user()->bagian->nama_bagian }}</p>

            <form method="POST" action="{{ route('kabag.tugas.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Ditugaskan ke</label>
                    <select name="ditugaskan_ke" class="w-full rounded-lg border-gray-300" required>
                        <option value="">- Pilih staf -</option>
                        @foreach ($stafBagian as $staf)
                            <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                        @endforeach
                    </select>
                    @error('ditugaskan_ke') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Judul tugas</label>
                    <input type="text" name="judul" value="{{ old('judul') }}"
                           placeholder="contoh: Verifikasi kuesioner Susenas Kecamatan Sirimau"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('judul') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Deskripsi (opsional)</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Tenggat (opsional)</label>
                    <input type="date" name="tenggat" value="{{ old('tenggat') }}"
                           class="w-full rounded-lg border-gray-300">
                    @error('tenggat') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">
                        Berikan tugas
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
