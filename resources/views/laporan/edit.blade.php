{{-- resources/views/laporan/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Laporan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui laporan aktivitas harian Anda.</p>
        </div>

        @if ($laporan->status === 'dikembalikan' && $laporan->logApproval->last())
            <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg mb-5">
                Catatan dari atasan: {{ $laporan->logApproval->last()->catatan ?? '-' }}
            </div>
        @endif

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form method="POST" action="{{ route('laporan.update', $laporan) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $laporan->tanggal->toDateString()) }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Terkait Tugas</label>
                        <select name="tugas_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                            <option value="">Tidak terkait tugas</option>
                            @foreach ($tugasAktif as $tugas)
                                <option value="{{ $tugas->id }}" @selected(old('tugas_id', $laporan->tugas_id) == $tugas->id)>{{ $tugas->judul }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $laporan->jam_mulai) }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $laporan->jam_selesai) }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                </div>

                @error('jam_selesai')
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5 rounded-lg mb-5">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </div>
                @enderror

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Uraian Kegiatan <span class="text-red-500">*</span></label>
                    <textarea name="uraian" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                              required>{{ old('uraian', $laporan->uraian) }}</textarea>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Output / Hasil</label>
                    <input type="text" name="output" value="{{ old('output', $laporan->output) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Lokasi <span class="text-red-500">*</span></label>
                        <select name="lokasi" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                            <option value="kantor" @selected(old('lokasi', $laporan->lokasi) === 'kantor')>Kantor</option>
                            <option value="lapangan" @selected(old('lokasi', $laporan->lokasi) === 'lapangan')>Lapangan</option>
                            <option value="dinas_luar" @selected(old('lokasi', $laporan->lokasi) === 'dinas_luar')>Dinas Luar</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Link Lampiran Bukti</label>
                        <input type="url" name="file_lampiran" value="{{ old('file_lampiran', $laporan->file_lampiran) }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                               placeholder="https://drive.google.com/file/d/...">
                        <p class="text-xs text-gray-400 mt-1">Paste link Google Drive bukti kegiatan</p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-5 border-t border-gray-100">
                    <a href="{{ route('laporan.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" name="aksi" value="draft"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Simpan Draft
                    </button>
                    <button type="submit" name="aksi" value="ajukan"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Kirim untuk Persetujuan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
