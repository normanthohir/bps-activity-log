@php
    $bagianAwal = (string) old('bagian_id', $induk?->bagian_id);
    $penerimaAwal = collect(old('penerima_ids', old('ditugaskan_ke') ? [old('ditugaskan_ke')] : []))
        ->map(fn ($v) => (string) $v)->values()->all();
    $pilihanKamus = $daftarKamus->map(fn ($k) => [
        'id' => (string) $k->id, 'nama' => $k->nama,
        'input' => $k->input, 'proses' => $k->proses, 'output' => $k->output,
        'bagian_id' => $k->bagian_id ? (string) $k->bagian_id : null,
    ])->values()->all();
@endphp
<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8 space-y-5" x-data="{
    bagian: @js($bagianAwal), jenis: @js(old('jenis_input','manual')),
    inputTugasId: @js((string) old('input_tugas_id','')),
    sumberJudul: @js(old('sumber_judul','manual')),
    kamusId: @js((string) old('kamus_kerja_id','')),
    daftarKamus: @js($pilihanKamus),
    penerimaIds: @js($penerimaAwal),
    judul: @js(old('judul','')), uraian: @js(old('uraian_input','')),
    proses: @js(old('deskripsi','')), output: @js(old('output_rencana','')),
    drafManual: null,
    tersediaKamus() { return this.daftarKamus.filter(k => !k.bagian_id || k.bagian_id === this.bagian); },
    pilihanKamus() { return this.daftarKamus.find(k => k.id === this.kamusId) || null; },
    gantiTim() {
        this.penerimaIds = [];
        if (this.pilihanKamus()?.bagian_id && this.pilihanKamus().bagian_id !== this.bagian) this.kamusId = '';
    },
    gantiSumber() {
        if (this.sumberJudul === 'kamus') {
            this.drafManual = {judul:this.judul,uraian:this.uraian,proses:this.proses,output:this.output};
            if (this.kamusId) this.isiDariKamus();
        } else {
            this.kamusId = '';
            if (this.drafManual) {
                this.judul=this.drafManual.judul; this.uraian=this.drafManual.uraian;
                this.proses=this.drafManual.proses; this.output=this.drafManual.output;
            }
        }
    },
    isiDariKamus() {
        const k = this.pilihanKamus(); if (!k) return;
        this.judul = k.nama || ''; this.uraian = k.input || '';
        this.proses = k.proses || ''; this.output = k.output || '';
        this.jenis = 'manual'; this.inputTugasId = '';
    }
}">
    <div><h1 class="text-2xl font-bold">{{ $induk ? 'Disposisi pekerjaan ke bawahan' : 'Tambah pekerjaan baru' }}</h1>
        <p class="text-sm text-slate-500">Pilih judul manual atau Kamus Kerja. Satu pekerjaan dapat ditugaskan ke beberapa pelaksana, termasuk Ketua Tim sendiri.</p></div>
    @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('pekerjaan.store') }}" class="bg-white rounded-xl border p-6 space-y-5">@csrf
        @if($induk)<input type="hidden" name="induk_tugas_id" value="{{ $induk->id }}"><div class="rounded bg-blue-50 p-3 text-sm">Induk disposisi: <b>{{ $induk->judul }}</b> (penerima: {{ $induk->penerimaTugas?->name }})</div>@endif
        <section class="border rounded-xl p-4 space-y-3">
            <h2 class="font-semibold text-blue-900">Dasar judul pekerjaan</h2>
            <p class="text-xs text-slate-500">Gunakan Kamus Kerja untuk mengisi rincian awal. Semua kolom yang terisi boleh diubah sebelum disimpan. Langkah SOP dibagikan melalui menu SOP → Beri Tugas.</p>
            <div class="flex flex-wrap gap-5 text-sm">
                <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sumber_judul" value="manual" x-model="sumberJudul" @change="gantiSumber()" class="text-blue-800"> Ketik manual</label>
                <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="sumber_judul" value="kamus" x-model="sumberJudul" @change="gantiSumber()" class="text-blue-800"> Pilih dari Kamus Kerja</label>
            </div>
            <div x-show="sumberJudul === 'kamus'" x-cloak class="bg-blue-50 rounded-lg border border-blue-100 p-4 space-y-3">
                <label class="block text-sm font-medium">Kamus Kerja terdaftar *</label>
                <select name="kamus_kerja_id" x-model="kamusId" @change="isiDariKamus()" :required="sumberJudul==='kamus'" :disabled="sumberJudul!=='kamus'" class="w-full rounded-lg border-slate-300">
                    <option value="">Pilih nama pekerjaan</option>
                    <template x-for="k in tersediaKamus()" :key="k.id"><option :value="k.id" x-text="k.nama + (k.bagian_id ? ' (Kamus Tim)' : ' (Kamus Kantor)')"></option></template>
                </select>
                <div class="flex items-center flex-wrap gap-3 text-sm"><button type="button" class="bg-white border border-blue-800 text-blue-800 rounded-lg px-3 py-2" @click="isiDariKamus()">Isi ulang dari Kamus</button>
                    @if(auth()->user()->isAdmin() || \App\Support\ModeKerja::ketuaAktif(auth()->user()))<a href="{{ route('kamus.index') }}" class="text-blue-800 underline">Kelola / Import Kamus Kerja →</a>@endif
                </div>
                @if($daftarKamus->isEmpty())<p class="text-sm text-amber-900">Belum ada Kamus Kerja. <a href="{{ route('kamus.index') }}" class="underline">Tambahkan dahulu</a>.</p>@endif
            </div>
            <div><label for="judul-pekerjaan" class="block text-sm font-medium mb-1">Judul pekerjaan *</label><input id="judul-pekerjaan" name="judul" required maxlength="255" x-model="judul" class="w-full rounded-lg border-slate-300" placeholder="Tulis judul pekerjaan atau pilih dari kamus"></div>
        </section>
        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="block font-medium text-sm mb-1">Tim *</label><select required name="bagian_id" x-model="bagian" @change="gantiTim()" class="w-full rounded-lg border-slate-300"><option value="">Pilih tim</option>@foreach($bagian as $tim)<option value="{{ $tim->id }}">{{ $tim->kode_bagian }} — {{ $tim->nama_bagian }}</option>@endforeach</select></div>
            <div class="border rounded-lg p-3 bg-slate-50">
                <label class="block font-medium text-sm mb-2">Penerima / pelaksana * <span class="text-slate-500 font-normal">(boleh banyak)</span></label>
                <p class="text-xs text-slate-500 mb-2">Pilih tim terlebih dahulu. Centang satu atau beberapa orang, termasuk Anda sendiri jika menjadi Ketua Tim.</p>
                <div class="max-h-52 overflow-y-auto grid sm:grid-cols-2 gap-2">
                    @foreach($penerima as $p)
                        <label class="flex items-start gap-2 text-sm p-1 rounded" :class="bagian && @js($p->allowed_tim_ids).includes(bagian) ? 'text-slate-800' : 'text-slate-400'">
                            <input type="checkbox" name="penerima_ids[]" value="{{ $p->id }}" x-model="penerimaIds"
                                   :disabled="!bagian || !@js($p->allowed_tim_ids).includes(bagian)" class="mt-1 rounded text-blue-800">
                            <span>{{ $p->name }} @if($p->id===auth()->id())<span class="font-semibold text-blue-800">(Saya sendiri)</span>@endif</span>
                        </label>
                    @endforeach
                </div>
                <div class="text-xs text-blue-800 mt-2"><span x-text="penerimaIds.length"></span> pelaksana dipilih. Masing-masing mendapat tugas dan progres sendiri.</div>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4"><div><label class="block font-medium text-sm mb-1">Tanggal mulai</label><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->toDateString()) }}" class="w-full rounded-lg border-slate-300"></div><div><label class="block font-medium text-sm mb-1">Tenggat</label><input type="date" name="tenggat" value="{{ old('tenggat') }}" class="w-full rounded-lg border-slate-300"></div></div>
        <section class="border rounded-xl p-4 space-y-3"><h2 class="font-bold text-blue-900">1. INPUT <span class="text-xs font-normal text-slate-500">(opsional)</span></h2>
            <label class="block text-sm">Jenis sumber pekerjaan</label><select name="jenis_input" x-model="jenis" class="w-full rounded-lg border-slate-300"><option value="manual">Uraian / penugasan langsung</option><option value="surat">Surat dari pusat / provinsi / lainnya</option><option value="output_tugas">Output pekerjaan sebelumnya</option></select>
            <div x-show="jenis === 'output_tugas'"><label class="block text-sm mb-1">Pekerjaan sumber output *</label><select name="input_tugas_id" x-model="inputTugasId" class="w-full rounded-lg border-slate-300"><option value="">Pilih pekerjaan sumber</option>@foreach($sumber as $src)<option value="{{ $src->id }}">{{ $src->judul }} · {{ $src->status }}</option>@endforeach</select><p class="text-xs text-slate-500">Pekerjaan lanjutan baru dapat dikerjakan setelah sumber output selesai.</p></div>
            <div x-show="jenis === 'surat'" class="grid md:grid-cols-3 gap-3"><div><label class="text-sm">Nomor surat</label><input name="nomor_surat" value="{{ old('nomor_surat') }}" class="w-full rounded-lg border-slate-300"></div><div><label class="text-sm">Asal surat</label><input name="asal_surat" value="{{ old('asal_surat') }}" placeholder="BPS RI / BPS Provinsi" class="w-full rounded-lg border-slate-300"></div><div><label class="text-sm">Tanggal surat</label><input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" class="w-full rounded-lg border-slate-300"></div></div>
            <label class="block text-sm">Uraian / keterangan input</label><textarea name="uraian_input" rows="2" x-model="uraian" class="w-full rounded-lg border-slate-300"></textarea>
            <label class="block text-sm">Lampiran input (maks. 10 MB)</label><input type="file" name="file_input" class="block text-sm w-full"></section>
        <section class="border rounded-xl p-4"><h2 class="font-bold text-blue-900 mb-2">2. PROSES <span class="text-xs font-normal text-slate-500">(opsional)</span></h2><textarea name="deskripsi" rows="4" x-model="proses" class="w-full rounded-lg border-slate-300" placeholder="Langkah kerja / proses yang dilakukan (jika kosong, sistem memakai judul pekerjaan)"></textarea></section>
        <section class="border rounded-xl p-4 space-y-3"><h2 class="font-bold text-blue-900">3. OUTPUT <span class="text-xs font-normal text-slate-500">(opsional saat pembuatan)</span></h2><div><label class="block text-sm">Output yang direncanakan</label><textarea name="output_rencana" rows="2" x-model="output" class="w-full rounded-lg border-slate-300"></textarea></div><div><label class="block text-sm">Output aktual (dapat diisi setelah dikerjakan)</label><textarea name="output_realisasi" rows="2" class="w-full rounded-lg border-slate-300">{{ old('output_realisasi') }}</textarea></div><input type="file" name="file_output" class="block text-sm"></section>
        <div class="flex gap-3 justify-end"><a href="{{ route('pekerjaan.index') }}" class="px-4 py-2 rounded-lg border">Batal</a><button type="submit" class="px-5 py-2 rounded-lg bg-[#1F3864] text-white font-medium">Simpan &amp; Bagikan Tugas</button></div>
    </form>
</div>
</x-app-layout>
