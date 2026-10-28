@php
    $awal = old(
        'langkah',
        $sop
            ? $sop->langkah
                ->map(
                    fn($l) => [
                        'input' => $l->input,
                        'proses' => $l->proses,
                        'output' => $l->output,
                        'sumber_input' => $l->sumber_input,
                        'durasi_hari' => $l->durasi_hari,
                    ],
                )
                ->toArray()
            : [['input' => '', 'proses' => '', 'output' => '', 'sumber_input' => 'manual', 'durasi_hari' => 1]],
    );
    $langkahAwal = array_values($awal);
@endphp
<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8" x-data="{ rows: @js($langkahAwal) }">
        <h1 class="text-2xl font-bold mb-2">{{ $sop ? 'Edit' : 'Deklarasi' }} SOP</h1>
        <p class="text-sm text-slate-500 mb-5">Tiap langkah punya IPO. Sumber 'Output langkah sebelumnya' membentuk
            ketergantungan otomatis saat dijalankan.</p>
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-5 text-sm">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ $sop ? route('sop.update', $sop) : route('sop.store') }}" class="space-y-4"
            x-data="{ loading: false }" x-on:submit="loading = true">
            @csrf
            @if ($sop)
                @method('PUT')
            @endif
            <div class="bg-white border p-5 rounded-xl grid md:grid-cols-3 gap-4">
                @if (!auth()->user()->isAdmin())
                    <div class="md:col-span-3"><label class="text-sm block font-semibold">Tim pemilik SOP *</label>
                        <select name="bagian_id" required class="w-full rounded-lg border-slate-300">
                            <option value="">Pilih tim yang Anda pimpin</option>
                            @foreach ($timDipimpin as $tim)
                                <option value="{{ $tim->id }}" @selected(old('bagian_id', $sop?->bagian_id) == $tim->id)>{{ $tim->kode_bagian }}
                                    — {{ $tim->nama_bagian }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">SOP ini hanya boleh langsung ditugaskan oleh Ketua Tim
                            untuk tim yang dipilih. Bila Anda memimpin beberapa tim, pilih salah satu tim.</p>
                    </div>
                @else<div class="md:col-span-3 p-3 bg-blue-50 rounded-lg text-sm">
                        {{ $sop?->bagian_id ? 'SOP milik tim tetap terhubung dengan tim awalnya.' : 'SOP yang dibuat Admin menjadi SOP kantor. Kepala BPS akan mendelegasikannya ke Ketua Tim.' }}
                    </div>
                @endif
                <div><label class="text-sm block">Kode SOP *</label><input required name="kode"
                        value="{{ old('kode', $sop?->kode) }}" class="w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm block">Nama SOP *</label><input required name="nama"
                        value="{{ old('nama', $sop?->nama) }}" class="w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm block">Versi *</label><input required name="versi"
                        value="{{ old('versi', $sop?->versi ?? '1.0') }}" class="w-full rounded-lg border-slate-300">
                </div>
                <div class="md:col-span-3"><label class="text-sm block">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full rounded-lg border-slate-300">{{ old('keterangan', $sop?->keterangan) }}</textarea>
                </div>
            </div>
            <template x-for="(row,i) in rows" :key="i">
                <section class="bg-white border rounded-xl p-5 space-y-3">
                    <div class="flex justify-between">
                        <h2 class="font-bold text-blue-900" x-text="'Langkah '+(i+1)"></h2><button type="button"
                            x-show="rows.length>1" @click="rows.splice(i,1)" class="text-red-600 text-sm">Hapus
                            langkah</button>
                    </div>
                    <div><label class="block text-sm">Input yang dibutuhkan</label>
                        <textarea x-model="row.input" :name="`langkah[${i}][input]`" rows="2" class="w-full rounded-lg border-slate-300"></textarea>
                    </div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div><label class="block text-sm">Jenis sumber input</label><select x-model="row.sumber_input"
                                :name="`langkah[${i}][sumber_input]`" class="w-full rounded-lg border-slate-300">
                                <option value="manual">Manual / data kerja</option>
                                <option value="surat">Surat disposisi</option>
                                <option value="sebelumnya">Output langkah sebelumnya</option>
                            </select></div>
                        <div><label class="block text-sm">Durasi (hari sejak langkah sebelumnya)</label><input required
                                type="number" min="0" max="3650" x-model="row.durasi_hari"
                                :name="`langkah[${i}][durasi_hari]`" class="w-full rounded-lg border-slate-300"></div>
                    </div>
                    <div><label class="block text-sm">Proses / pekerjaan *</label>
                        <textarea required rows="3" x-model="row.proses" :name="`langkah[${i}][proses]`"
                            class="w-full rounded-lg border-slate-300"></textarea>
                    </div>
                    <div><label class="block text-sm">Output yang diharapkan</label>
                        <textarea rows="2" x-model="row.output" :name="`langkah[${i}][output]`"
                            class="w-full rounded-lg border-slate-300"></textarea>
                    </div>
                </section>
            </template>
            <x-loading-overlay message="Menyimpan..." />
            <div class="flex flex-wrap justify-between gap-3">
                <button type="button" class="border border-blue-700 text-blue-700 rounded-lg px-4 py-2 text-sm"
                    @click="rows.push({input:'',proses:'',output:'',sumber_input:'sebelumnya',durasi_hari:1})">+ Tambah
                    langkah</button>
                <div class="flex gap-2"><a href="{{ route('sop.index') }}"
                        class="px-4 py-2 border rounded-lg">Batal</a>
                    <button class="bg-[#1F3864] text-white rounded-lg px-5 py-2">Simpan SOP</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
