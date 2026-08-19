{{-- resources/views/tugas/create-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-1">Beri Tugas</p>
            <p class="text-sm text-gray-500 mb-5">Kepala BPS — bisa lintas semua bagian</p>

            <form method="POST" action="{{ route('kepala-bps.tugas.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Pilih bagian</label>
                    <select id="bagian_id" name="bagian_id" class="w-full rounded-lg border-gray-300" required>
                        <option value="">- Pilih bagian -</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}" @selected(old('bagian_id') == $bagian->id)>
                                {{ $bagian->nama_bagian }}
                            </option>
                        @endforeach
                    </select>
                    @error('bagian_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Ditugaskan ke</label>
                    <select id="ditugaskan_ke" name="ditugaskan_ke" class="w-full rounded-lg border-gray-300" required disabled>
                        <option value="">- Pilih bagian terlebih dahulu -</option>
                    </select>
                    @error('ditugaskan_ke') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Judul tugas</label>
                    <input type="text" name="judul" value="{{ old('judul') }}"
                           placeholder="contoh: Koordinasi persiapan Sensus Ekonomi"
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

    {{-- Data pegawai per bagian dikirim sebagai JSON, lalu di-filter
         dengan JavaScript murni tanpa perlu request tambahan ke server --}}
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

        // Kalau form submit gagal validasi dan bagian sudah sempat dipilih
        // sebelumnya (old value), langsung muat ulang daftar pegawainya.
        if (selectBagian.value) {
            muatDaftarPegawai(selectBagian.value);
        }
    </script>
</x-app-layout>
