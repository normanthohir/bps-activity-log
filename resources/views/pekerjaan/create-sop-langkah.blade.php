<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8 space-y-5">
    <div class="text-sm flex flex-wrap gap-3"><a class="text-blue-700 underline" href="{{ route('sop.index') }}">SOP</a><a class="text-blue-700 underline" href="{{ route('sop.delegasi.langkah', $pelaksanaan) }}">← Daftar langkah dan pelaksana</a></div>
    <div><h1 class="text-2xl font-bold">Beri Tugas — Langkah {{ $langkah->urutan }}</h1>
        <p class="text-sm text-slate-500">{{ $pelaksanaan->sop->kode }} · {{ $pelaksanaan->nama }} · Tim {{ $induk->bagian->kode_bagian }} — {{ $induk->bagian->nama_bagian }}</p></div>
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm">
        <strong>Rincian sudah terisi dari SOP.</strong> Anda boleh mengubah judul, input, proses, output dan jadwal sebelum menyimpan.
        Satu atau beberapa pegawai dari <strong>tim yang sama</strong>, termasuk Anda, bisa dipilih. Setiap pegawai menerima pekerjaan sendiri.
        @if($existing->isNotEmpty()) <div class="mt-2">Sudah ditugaskan kepada: {{ $existing->pluck('penerimaTugas.name')->filter()->implode(', ') }}. Pilih pegawai tambahan saja.</div>@endif
    </div>
    @if($errors->any())<div class="bg-red-50 border border-red-200 p-4 text-sm text-red-800">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('sop.delegasi.simpan-langkah', $pelaksanaan) }}" class="bg-white border rounded-xl p-5 space-y-5">@csrf
        <input type="hidden" name="sop_langkah_id" value="{{ $langkah->id }}">
        <section class="space-y-2"><h2 class="font-bold text-blue-900">Pegawai pelaksana (pilih satu atau lebih) *</h2>
            <div class="grid md:grid-cols-2 gap-2 p-3 border rounded-lg bg-slate-50">
                @foreach($anggota as $pegawai)
                    <label class="flex gap-2 items-start text-sm rounded p-1 hover:bg-white"><input type="checkbox" name="penerima_ids[]" value="{{ $pegawai->id }}" class="rounded border-slate-300 mt-1" @checked(in_array((string)$pegawai->id, array_map('strval', (array)old('penerima_ids', [])), true))>
                        <span>{{ $pegawai->name }} ({{ $pegawai->nip }}) @if($pegawai->id === auth()->id())<strong class="text-blue-700">— Saya sendiri</strong>@endif</span></label>
                @endforeach
            </div>
        </section>
        <div><label class="block text-sm font-medium mb-1">Judul pekerjaan *</label><input required name="judul" maxlength="255" class="w-full rounded-lg border-slate-300" value="{{ old('judul', \Illuminate\Support\Str::limit($pelaksanaan->nama.' — Langkah '.$langkah->urutan.' · '.$langkah->proses, 250)) }}"></div>
        <div class="grid md:grid-cols-2 gap-4"><div><label class="text-sm block mb-1">Tanggal mulai *</label><input type="date" name="tanggal_mulai" required value="{{ old('tanggal_mulai', $awal->toDateString()) }}" class="w-full rounded-lg border-slate-300"></div>
            <div><label class="text-sm block mb-1">Tenggat *</label><input type="date" name="tenggat" required value="{{ old('tenggat', $tenggat->toDateString()) }}" class="w-full rounded-lg border-slate-300"></div></div>
        <section class="border rounded-xl p-4 space-y-3"><h2 class="font-semibold text-blue-900">1. INPUT</h2>
            <p class="text-xs text-slate-500">Jenis sumber: {{ $langkah->sumber_input === 'sebelumnya' ? 'Output seluruh pelaksana langkah sebelumnya (hubungan otomatis)' : ($langkah->sumber_input === 'surat' ? 'Surat disposisi' : 'Manual') }}. Jenis/relasi sumber dijaga aplikasi agar alur SOP tidak rusak.</p>
            <textarea name="uraian_input" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Uraian input">{{ old('uraian_input', $langkah->input) }}</textarea></section>
        <section class="border rounded-xl p-4 space-y-3"><h2 class="font-semibold text-blue-900">2. PROSES</h2>
            <textarea required name="deskripsi" rows="4" class="w-full rounded-lg border-slate-300">{{ old('deskripsi', $langkah->proses) }}</textarea>
            <label class="text-sm block">Arahan tambahan (opsional)</label><textarea name="catatan" rows="2" class="w-full rounded-lg border-slate-300">{{ old('catatan') }}</textarea></section>
        <section class="border rounded-xl p-4 space-y-3"><h2 class="font-semibold text-blue-900">3. OUTPUT RENCANA</h2>
            <textarea name="output_rencana" rows="3" class="w-full rounded-lg border-slate-300">{{ old('output_rencana', $langkah->output) }}</textarea>
            <p class="text-xs text-slate-500">Output aktual nanti diisi oleh setiap pelaksana pada detail pekerjaan masing-masing.</p></section>
        <div class="flex gap-3 justify-end"><a class="px-4 py-2 border rounded-lg" href="{{ route('sop.delegasi.langkah', $pelaksanaan) }}">Batal</a><button class="px-5 py-2 rounded-lg text-white bg-[#1F3864]">Simpan & Beri Tugas</button></div>
    </form>
</div>
</x-app-layout>
