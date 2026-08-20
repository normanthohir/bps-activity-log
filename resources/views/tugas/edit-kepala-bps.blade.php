{{-- resources/views/tugas/edit-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Tugas</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Ditugaskan ke {{ $tugas->penerimaTugas->name }} &middot; {{ $tugas->bagian->nama_bagian }}
                <br><span class="text-xs text-gray-400">Penerima tugas tidak bisa diubah — hapus dan buat ulang jika perlu ganti penerima.</span>
            </p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form method="POST" action="{{ route('kepala-bps.tugas.update', $tugas) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Judul Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" value="{{ old('judul', $tugas->judul) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('judul') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Tenggat Waktu</label>
                    <input type="date" name="tenggat" value="{{ old('tenggat', $tugas->tenggat?->toDateString()) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    @error('tenggat') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('kepala-bps.tugas.index') }}"
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
