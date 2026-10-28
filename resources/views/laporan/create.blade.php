{{-- resources/views/laporan/create.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Laporan Aktivitas Harian</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ auth()->user()->name }} &middot;
                {{ auth()->user()->bagian->nama_bagian }}</p>
        </div>

        @if ($tugasTerpilih ?? false)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg mb-5">
                Laporan ini akan dikaitkan dengan tugas yang dipilih di bawah.
            </div>
        @endif

        {{-- Form card (JavaScript Validasi Dihapus, Hanya Menyisakan State Loading & Toggle Alasan) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{
            loading: false,
            output: '{{ old('output', '') }}',
            submit(aksi) {
                this.loading = true;
                this.$refs.form.querySelector('[name=aksi]').value = aksi;
                this.$refs.form.submit();
            }
        }">

            @if (auth()->user()->role === 'staf')
                <form method="POST" action="{{ route('laporan.store') }}" class="p-6" x-ref="form">
                @else
                    <form method="POST" action="{{ route('kabag.laporan.store') }}" class="p-6" x-ref="form">
            @endif

            @csrf
            <input type="hidden" name="aksi" value="draft">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Tanggal <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                        class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('tanggal') border-red-500 @else border-gray-300 @enderror">
                    @error('tanggal')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Terkait Pekerjaan</label>
                    <select name="tugas_id"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">Tidak terkait Pekerjaan</option>
                        @foreach ($tugasAktif as $tugas)
                            <option value="{{ $tugas->id }}" @selected(old('tugas_id', $tugasTerpilih ?? null) == $tugas->id)>
                                {{ $tugas->judul }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Terkait pekerjaan akan tampil apa bila di status pekerjaan "selesai"</p>
                </div>
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1.5">Uraian Kegiatan <span
                        class="text-red-500">*</span></label>
                <textarea name="uraian" rows="4" required placeholder="Jelaskan kegiatan yang dilakukan hari ini..."
                    class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('uraian') border-red-500 @else border-gray-300 @enderror">{{ old('uraian') }}</textarea>
                @error('uraian')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Lokasi <span
                            class="text-red-500">*</span></label>
                    <select name="lokasi" required
                        class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('lokasi') border-red-500 @else border-gray-300 @enderror">
                        <option value="" disabled @selected(!old('lokasi'))>Pilih Opsi Lokasi</option>
                        <option value="kantor" @selected(old('lokasi') === 'kantor')>Kantor</option>
                        <option value="lapangan" @selected(old('lokasi') === 'lapangan')>Lapangan</option>
                        <option value="dinas_luar" @selected(old('lokasi') === 'dinas_luar')>Dinas Luar</option>
                    </select>
                    @error('lokasi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">
                        Output / Hasil <span class="text-red-500">*</span>
                    </label>
                    <select name="output" x-model="output" required
                        class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('output') border-red-500 @else border-gray-300 @enderror">
                        <option value="" disabled>Pilih opsi</option>
                        <option value="selesai">Selesai</option>
                        <option value="tertunda">Tertunda</option>
                        <option value="gagal">Gagal</option>
                    </select>
                    @error('output')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                {{-- Kolom alasan: hanya muncul kalau output gagal atau tertunda via Alpine tunggal --}}
                <div x-show="output === 'gagal' || output === 'tertunda'" x-cloak x-transition>
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">
                        <span x-text="output === 'gagal' ? 'Alasan Gagal' : 'Alasan Tertunda'"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alasan" rows="2" placeholder="Jelaskan secara detail kendalanya..."
                        class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('alasan') border-red-500 @else border-gray-300 @enderror">{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <div class="mb-6">
                <label class="text-sm font-medium text-gray-700 block mb-1.5">Link Lampiran (Opsional)</label>
                <input type="url" name="file_lampiran" value="{{ old('file_lampiran') }}"
                    class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('file_lampiran') border-red-500 @else border-gray-300 @enderror"
                    placeholder="Contoh: https://google.com... ">
                <p class="text-xs text-gray-400 mt-1">Paste link Google Drive bukti kegiatan</p>
                @error('file_lampiran')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <x-loading-overlay message="Mengirim laporan..." />
            {{-- Tombol Aksi Form --}}
            <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                <button type="button" @click="submit('ajukan')" :disabled="loading"
                    class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] text-white hover:bg-[#1a3056] transition-colors disabled:opacity-50">
                    <span x-text="loading ? 'Mengirim Laporan...' : 'Kirim Laporan'"></span>
                </button>
            </div>

            </form>
        </div>
    </div>
</x-app-layout>
