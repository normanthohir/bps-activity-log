{{-- resources/views/tugas/create-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Beri Tugas Baru</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kepala BPS — bisa lintas semua bagian.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form method="POST" action="{{ route('kepala-bps.tugas.store') }}" class="p-6">
                @csrf

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Pilih Bagian <span class="text-red-500">*</span></label>
                    <select id="bagian_id" name="bagian_id"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                        <option value="">— Pilih Bagian —</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}" @selected(old('bagian_id') == $bagian->id)>{{ $bagian->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Ditugaskan Ke <span class="text-red-500">*</span></label>
                    <select id="ditugaskan_ke" name="ditugaskan_ke"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required disabled>
                        <option value="">— Pilih bagian terlebih dahulu —</option>
                    </select>
                    @error('ditugaskan_ke') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Judul Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" value="{{ old('judul') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                           placeholder="Contoh: Koordinasi persiapan Sensus Ekonomi" required>
                    @error('judul') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                              placeholder="Jelaskan detail pekerjaan yang perlu dilakukan...">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Tenggat Waktu</label>
                    <input type="date" name="tenggat" value="{{ old('tenggat') }}"
                           min="{{ now()->toDateString() }}"
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
                        Berikan Tugas
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        const dataPegawaiPerBagian = @json($dataPegawaiPerBagian);
        const selectBagian = document.getElementById('bagian_id');
        const selectPegawai = document.getElementById('ditugaskan_ke');
        const nilaiTerpilihSebelumnya = "{{ old('ditugaskan_ke') }}";

        function muatDaftarPegawai(bagianId) {
            selectPegawai.innerHTML = '';
            const daftar = dataPegawaiPerBagian[bagianId] ?? [];
            if (daftar.length === 0) {
                selectPegawai.disabled = true;
                selectPegawai.innerHTML = '<option value="">- Tidak ada pegawai di bagian ini -</option>';
                return;
            }
            selectPegawai.disabled = false;
            selectPegawai.innerHTML = '<option value="">- Pilih staf/kepala bagian -</option>';
            daftar.forEach(function (pegawai) {
                const opt = document.createElement('option');
                opt.value = pegawai.id;
                opt.textContent = pegawai.label;
                if (String(pegawai.id) === nilaiTerpilihSebelumnya) {
                    opt.selected = true;
                }
                selectPegawai.appendChild(opt);
            });
        }

        selectBagian.addEventListener('change', function () {
            muatDaftarPegawai(this.value);
        });

        if (selectBagian.value) {
            muatDaftarPegawai(selectBagian.value);
        }
    </script>
</x-app-layout>
