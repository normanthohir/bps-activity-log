{{-- resources/views/laporan/create.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-1">Laporan aktivitas harian</p>
            <p class="text-sm text-gray-500 mb-5">
                {{ auth()->user()->name }} · {{ auth()->user()->bagian->nama_bagian }}
            </p>

            @if ($tugasTerpilih)
                <div class="bg-blue-50 text-blue-700 text-sm p-3 rounded-lg mb-5">
                    Laporan ini akan dikaitkan dengan tugas yang dipilih di bawah.
                </div>
            @endif

            <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                            class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Terkait tugas</label>
                        <select name="tugas_id" class="w-full rounded-lg border-gray-300">
                            <option value="">Tidak terkait tugas</option>
                            @foreach ($tugasAktif as $tugas)
                                <option value="{{ $tugas->id }}" @selected(old('tugas_id', $tugasTerpilih) == $tugas->id)>
                                    {{ $tugas->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Jam mulai</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}"
                            class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Jam selesai</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}"
                            class="w-full rounded-lg border-gray-300">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Uraian kegiatan</label>
                    <textarea name="uraian" rows="3" class="w-full rounded-lg border-gray-300" required>{{ old('uraian') }}</textarea>
                </div>


                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Lokasi</label>
                        <select name="lokasi" class="w-full rounded-lg border-gray-300" required>
                            <option value="kantor">Kantor</option>
                            <option value="lapangan">Lapangan</option>
                            <option value="dinas_luar">Dinas luar</option>
                        </select>
                    </div>
                    <div >
                        <label class="text-sm text-gray-500 block mb-1">Output/hasil</label>
                        <input type="text" name="output" value="{{ old('output') }}"
                            class="w-full rounded-lg border-gray-300">
                    </div>

                </div>
                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Lampiran bukti</label>
                    <input type="url" name="file_lampiran" class="w-full rounded-lg border-gray-300"
                        value="{{ old('file_lampiran') }}" placeholder="https://contoh.com">
                    {{-- <input type="link" name="file_lampiran" class="w-full text-sm"> --}}
                </div>

                @error('jam_selesai')
                    <p class="text-sm text-red-600 mb-4">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" name="aksi" value="draft"
                        class="px-4 py-2 text-sm rounded-lg border">Simpan draft</button>
                    <button type="submit" name="aksi" value="ajukan"
                        class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">
                        Kirim untuk persetujuan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
