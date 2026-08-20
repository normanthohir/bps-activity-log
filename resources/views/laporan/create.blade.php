{{-- resources/views/laporan/create.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Laporan Aktivitas Harian</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ auth()->user()->name }} &middot; {{ auth()->user()->bagian->nama_bagian }}</p>
        </div>

        @if ($tugasTerpilih ?? false)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg mb-5">
                Laporan ini akan dikaitkan dengan tugas yang dipilih di bawah.
            </div>
        @endif

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 show: false,
                 errors: {},
                 tanggal: '{{ old('tanggal', now()->toDateString()) }}',
                 jam_mulai: '{{ old('jam_mulai') }}',
                 jam_selesai: '{{ old('jam_selesai') }}',
                 uraian: '{{ old('uraian') }}',
                 output: '{{ old('output') }}',
                 lokasi: '{{ old('lokasi', 'kantor') }}',
                 file_lampiran: '{{ old('file_lampiran') }}',
                 validate() {
                     this.errors = {};
                     if (!this.tanggal) this.errors.tanggal = 'Tanggal wajib diisi.';
                     if (!this.uraian.trim()) this.errors.uraian = 'Uraian kegiatan wajib diisi.';
                     if (!this.lokasi) this.errors.lokasi = 'Lokasi wajib dipilih.';
                     if (this.jam_mulai && this.jam_selesai && this.jam_selesai <= this.jam_mulai) {
                         this.errors.jam_selesai = 'Jam selesai harus setelah jam mulai.';
                     }
                     if (this.file_lampiran && !/^https?:\/\/.+/.test(this.file_lampiran)) {
                         this.errors.file_lampiran = 'Link lampiran harus berupa URL yang valid.';
                     }
                     return Object.keys(this.errors).length === 0;
                 },
                 submit(aksi) {
                     if (this.validate()) {
                         this.$refs.form.querySelector('[name=aksi]').value = aksi;
                         this.$refs.form.submit();
                     }
                 }
             }"
             x-init="$watch('show', v => { if(v) $el.scrollIntoView({behavior:'smooth',block:'start'}) })">

            <form method="POST" action="{{ route('laporan.store') }}" class="p-6" x-ref="form" novalidate>
                @csrf
                <input type="hidden" name="aksi" value="draft">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" x-model="tanggal"
                               class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                               :class="errors.tanggal ? 'border-red-500' : 'border-gray-300'">
                        <template x-if="errors.tanggal">
                            <p class="text-xs text-red-600 mt-1" x-text="errors.tanggal"></p>
                        </template>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Terkait Tugas</label>
                        <select name="tugas_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                            <option value="">Tidak terkait tugas</option>
                            @foreach ($tugasAktif as $tugas)
                                <option value="{{ $tugas->id }}" @selected(old('tugas_id', $tugasTerpilih ?? null) == $tugas->id)>{{ $tugas->judul }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-1">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" x-model="jam_mulai"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" x-model="jam_selesai"
                               class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                               :class="errors.jam_selesai ? 'border-red-500' : 'border-gray-300'">
                        <template x-if="errors.jam_selesai">
                            <p class="text-xs text-red-600 mt-1" x-text="errors.jam_selesai"></p>
                        </template>
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

                <div class="mb-1">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Uraian Kegiatan <span class="text-red-500">*</span></label>
                    <textarea name="uraian" rows="4" x-model="uraian"
                              class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                              :class="errors.uraian ? 'border-red-500' : 'border-gray-300'"
                              placeholder="Jelaskan kegiatan yang dilakukan hari ini..."></textarea>
                    <template x-if="errors.uraian">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.uraian"></p>
                    </template>
                </div>

                <div class="mb-1">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Output / Hasil</label>
                    <input type="text" name="output" x-model="output"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           placeholder="Contoh: Laporan statistik bulanan">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Lokasi <span class="text-red-500">*</span></label>
                        <select name="lokasi" x-model="lokasi"
                                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                                :class="errors.lokasi ? 'border-red-500' : 'border-gray-300'">
                            <option value="kantor">Kantor</option>
                            <option value="lapangan">Lapangan</option>
                            <option value="dinas_luar">Dinas Luar</option>
                        </select>
                        <template x-if="errors.lokasi">
                            <p class="text-xs text-red-600 mt-1" x-text="errors.lokasi"></p>
                        </template>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1.5">Link Lampiran Bukti</label>
                        <input type="url" name="file_lampiran" x-model="file_lampiran"
                               class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                               :class="errors.file_lampiran ? 'border-red-500' : 'border-gray-300'"
                               placeholder="https://drive.google.com/file/d/...">
                        <p class="text-xs text-gray-400 mt-1">Paste link Google Drive bukti kegiatan</p>
                        <template x-if="errors.file_lampiran">
                            <p class="text-xs text-red-600 mt-1" x-text="errors.file_lampiran"></p>
                        </template>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-5 border-t border-gray-100">
                    <button type="button" @click="submit('draft')"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Simpan Draft
                    </button>
                    <button type="button" @click="submit('ajukan')"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Kirim untuk Persetujuan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
