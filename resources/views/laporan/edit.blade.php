{{-- resources/views/laporan/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-1">
                {{ $laporan->status === 'dikembalikan' ? 'Revisi laporan' : 'Edit laporan (draft)' }}
            </p>
            <p class="text-sm text-gray-500 mb-5">{{ $laporan->tanggal->format('d M Y') }}</p>

            @if ($laporan->status === 'dikembalikan' && $laporan->logApproval->last())
                <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg mb-5">
                    Catatan dari atasan: {{ $laporan->logApproval->last()->catatan ?? '-' }}
                </div>
            @endif

            <form method="POST" action="{{ route('laporan.update', $laporan) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $laporan->tanggal->toDateString()) }}"
                               class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Terkait tugas</label>
                        <select name="tugas_id" class="w-full rounded-lg border-gray-300">
                            <option value="">Tidak terkait tugas</option>
                            @foreach ($tugasAktif as $tugas)
                                <option value="{{ $tugas->id }}" @selected(old('tugas_id', $laporan->tugas_id) == $tugas->id)>
                                    {{ $tugas->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Jam mulai</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $laporan->jam_mulai) }}"
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 block mb-1">Jam selesai</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $laporan->jam_selesai) }}"
                               class="w-full rounded-lg border-gray-300">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Uraian kegiatan</label>
                    <textarea name="uraian" rows="3" class="w-full rounded-lg border-gray-300"
                              required>{{ old('uraian', $laporan->uraian) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Output/hasil</label>
                    <input type="text" name="output" value="{{ old('output', $laporan->output) }}"
                           class="w-full rounded-lg border-gray-300">
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Lokasi</label>
                    <select name="lokasi" class="w-full rounded-lg border-gray-300" required>
                        @foreach (['kantor' => 'Kantor', 'lapangan' => 'Lapangan', 'dinas_luar' => 'Dinas luar'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('lokasi', $laporan->lokasi) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

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
