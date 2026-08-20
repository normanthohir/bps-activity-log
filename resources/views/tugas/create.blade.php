{{-- resources/views/tugas/create.blade.php --}}
@php
    $isKepalaBps = auth()->user()->isKepalaBps();
    $storeRoute = $isKepalaBps ? 'kepala-bps.tugas.store' : 'kabag.tugas.store';
    $indexRoute = $isKepalaBps ? 'kepala-bps.tugas.index' : 'kabag.tugas.index';
@endphp

<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Beri Tugas Baru</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                @if ($isKepalaBps)
                    Tentukan staf dari bagian mana pun untuk menerima tugas ini.
                @else
                    Berikan tugas ke staf di bagian {{ auth()->user()->bagian->nama_bagian }}.
                @endif
            </p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
             x-data="{
                 errors: {},
                 judul: '{{ old('judul') }}',
                 deskripsi: '{{ old('deskripsi') }}',
                 tenggat: '{{ old('tenggat') }}',
                 ditugaskan_ke: '{{ old('ditugaskan_ke') }}',
                 validate() {
                     this.errors = {};
                     if (!this.judul.trim()) this.errors.judul = 'Judul tugas wajib diisi.';
                     if (this.judul.length > 255) this.errors.judul = 'Judul maksimal 255 karakter.';
                     if (!this.ditugaskan_ke) this.errors.ditugaskan_ke = 'Pilih staf yang akan ditugaskan.';
                     if (this.tenggat && this.tenggat < '{{ now()->toDateString() }}') {
                         this.errors.tenggat = 'Tenggat tidak boleh di masa lalu.';
                     }
                     return Object.keys(this.errors).length === 0;
                 },
                 submit() {
                     if (this.validate()) {
                         this.$refs.form.submit();
                     }
                 }
             }">
            <form method="POST" action="{{ route($storeRoute) }}" class="p-6" x-ref="form" novalidate>
                @csrf

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Judul Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" x-model="judul"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.judul ? 'border-red-500' : 'border-gray-300'"
                           placeholder="Contoh: Input data sensus penduduk">
                    <template x-if="errors.judul">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.judul"></p>
                    </template>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" x-model="deskripsi"
                              class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                              placeholder="Jelaskan detail pekerjaan yang perlu dilakukan..."></textarea>
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Tenggat Waktu</label>
                    <input type="date" name="tenggat" x-model="tenggat"
                           min="{{ now()->toDateString() }}"
                           class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           :class="errors.tenggat ? 'border-red-500' : 'border-gray-300'">
                    <template x-if="errors.tenggat">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.tenggat"></p>
                    </template>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Ditugaskan Ke <span class="text-red-500">*</span></label>

                    @if ($isKepalaBps)
                        {{-- Kepala BPS: pilih dari semua staf --}}
                        <select name="ditugaskan_ke" x-model="ditugaskan_ke"
                                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                                :class="errors.ditugaskan_ke ? 'border-red-500' : 'border-gray-300'">
                            <option value="">— Pilih Staf —</option>
                            @foreach ($daftarBagian as $bagian)
                                @php $stafDiBagian = $daftarStaf->where('bagian_id', $bagian->id); @endphp
                                @if ($stafDiBagian->isNotEmpty())
                                    <optgroup label="{{ $bagian->nama_bagian }}">
                                        @foreach ($stafDiBagian as $staf)
                                            <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    @else
                        {{-- Kepala Bagian: hanya staf di bagian sendiri --}}
                        <select name="ditugaskan_ke" x-model="ditugaskan_ke"
                                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                                :class="errors.ditugaskan_ke ? 'border-red-500' : 'border-gray-300'">
                            <option value="">— Pilih Staf —</option>
                            @foreach ($stafBagian as $staf)
                                <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <template x-if="errors.ditugaskan_ke">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.ditugaskan_ke"></p>
                    </template>
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route($indexRoute) }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="button" @click="submit()"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Berikan Tugas
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
