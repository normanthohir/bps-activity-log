<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8 space-y-5">
    <a href="{{ route('pekerjaan.index') }}" class="text-sm text-blue-700">← Daftar pekerjaan</a>
    <div class="flex flex-wrap justify-between items-start gap-3"><div><h1 class="text-2xl font-bold text-slate-900">{{ $tugas->judul }}</h1><p class="text-sm text-slate-500 mt-1">{{ $tugas->bagian?->nama_bagian }} · {{ $tugas->penerimaTugas?->name }} · {{ ucwords(str_replace('_',' ',$tugas->status)) }} · Tenggat {{ $tugas->tenggat?->format('d/m/Y') ?? '—' }}</p></div>
    @if(\App\Support\AksesPekerjaan::bolehDisposisi(auth()->user(),$tugas) && !$tugas->adalahIndukDelegasiSop())<a href="{{ route('pekerjaan.create',['induk'=>$tugas->id]) }}" class="bg-[#1F3864] px-4 py-2 rounded-lg text-white text-sm font-semibold">↳ Disposisikan ke bawahan</a>@endif</div>
    @if($tugas->adalahIndukDelegasiSop())
        <section class="border border-blue-200 bg-blue-50 p-4 rounded-xl text-sm flex flex-wrap justify-between items-center gap-3">
            <p><strong>Delegasi SOP utuh.</strong> Ketua tim membagi tiap langkah kepada satu atau beberapa pelaksana (termasuk dirinya). Semua langkah dan hasil harus selesai sebelum tugas induk dapat ditutup.</p>
            @if(auth()->id() === $tugas->ditugaskan_ke && \App\Support\ModeKerja::ketuaAktif(auth()->user()) && auth()->user()->memimpinTim($tugas->bagian_id))
                <a class="bg-[#1F3864] text-white px-4 py-2 rounded-lg font-medium" href="{{ route('sop.delegasi.langkah', $tugas->sopPelaksanaan) }}">Kelola disposisi langkah SOP →</a>
            @endif
        </section>
    @endif
    @if($errors->any())<div class="bg-red-50 text-red-700 rounded-lg p-4">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <div class="grid md:grid-cols-3 gap-4">
        <section class="bg-white border rounded-xl p-5 space-y-3"><h2 class="font-bold text-blue-900">1 · INPUT</h2><p class="text-xs uppercase text-slate-500">{{ $tugas->jenis_input }}</p>
        @if($sumberOutput->isNotEmpty())
            <div class="space-y-2 text-sm">
                <p class="font-semibold">Output pekerjaan sumber ({{ $sumberOutput->count() }} pelaksana)</p>
                @foreach($sumberOutput as $sumber)
                    <div class="border-t pt-2">
                        <span class="text-slate-600">{{ $sumber->penerimaTugas?->name ?? 'Pelaksana' }} · {{ ucwords(str_replace('_', ' ', $sumber->status)) }}</span>
                        <div>
                            @if(\App\Support\AksesPekerjaan::dapatLihat(auth()->user(), $sumber))
                                <a class="text-blue-700 underline" href="{{ route('pekerjaan.show', $sumber) }}">{{ $sumber->judul }}</a>
                            @else
                                <strong>{{ $sumber->judul }}</strong>
                            @endif
                        </div>
                        <p class="text-slate-500 whitespace-pre-wrap">{{ $sumber->output_realisasi ?: $sumber->output_rencana ?: 'Output belum tersedia' }}</p>
                        @if($sumber->file_output)
                            <a class="text-blue-700 underline" href="{{ route('pekerjaan.berkas', [$sumber, 'output']) }}">Unduh output pelaksana ini</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        @if($tugas->jenis_input==='surat')<p class="text-sm">{{ $tugas->asal_surat }} · {{ $tugas->nomor_surat }} · {{ $tugas->tanggal_surat?->format('d/m/Y') }}</p>@endif
        <p class="text-sm whitespace-pre-wrap">{{ $tugas->uraian_input ?: 'Belum ada uraian input' }}</p>
        @if($tugas->file_input)<a class="text-blue-700 text-sm underline" href="{{ route('pekerjaan.berkas',[$tugas,'input']) }}">Unduh lampiran input</a>@endif</section>
        <section class="bg-white border rounded-xl p-5 space-y-3"><h2 class="font-bold text-blue-900">2 · PROSES</h2><p class="text-sm whitespace-pre-wrap">{{ $tugas->deskripsi ?: 'Tidak ada rincian proses.' }}</p><p class="text-xs text-slate-500">Mulai {{ $tugas->tanggal_mulai?->format('d/m/Y') ?? '—' }} · Tenggat {{ $tugas->tenggat?->format('d/m/Y') ?? '—' }}</p></section>
        <section class="bg-white border rounded-xl p-5 space-y-3"><h2 class="font-bold text-blue-900">3 · OUTPUT</h2><h3 class="text-xs uppercase font-semibold text-slate-500">Rencana</h3><p class="text-sm whitespace-pre-wrap">{{ $tugas->output_rencana ?: 'Belum ditentukan' }}</p><h3 class="text-xs uppercase font-semibold text-slate-500">Realisasi</h3><p class="text-sm whitespace-pre-wrap">{{ $tugas->output_realisasi ?: 'Belum tersedia' }}</p>@if($tugas->file_output)<a class="text-blue-700 text-sm underline" href="{{ route('pekerjaan.berkas',[$tugas,'output']) }}">Unduh output</a>@endif</section>
    </div>
    @if($tugas->induk)<div class="bg-indigo-50 rounded-xl p-4 text-sm">Disposisi turunan dari: @if(\App\Support\AksesPekerjaan::dapatLihat(auth()->user(),$tugas->induk))<a class="font-semibold text-blue-700" href="{{ route('pekerjaan.show',$tugas->induk) }}">{{ $tugas->induk->judul }}</a>@else <strong>{{ $tugas->induk->judul }}</strong>@endif</div>@endif
    @if($tugas->status !== 'selesai' && (auth()->user()->isAdmin() || auth()->user()->isKepalaBps()
        || $tugas->ditugaskan_ke === auth()->id()
        || (\App\Support\ModeKerja::ketuaAktif(auth()->user()) && auth()->user()->memimpinTim($tugas->bagian_id) && $tugas->dibuat_oleh === auth()->id())))
        <a class="inline-flex mb-3 px-3 py-2 bg-white border border-blue-300 rounded text-sm text-blue-800" href="{{ route('pekerjaan.edit-rincian', $tugas) }}">Edit rincian Input–Proses–Output & tanggal →</a>
    @endif
    @if($tugas->kamusKerja)
        <div class="rounded-xl bg-sky-50 border border-sky-100 p-4 text-sm">Berdasarkan Kamus Kerja: <strong>{{ $tugas->kamusKerja->nama }}</strong>. Rincian tugas disalin saat dibuat dan boleh diedit secara terpisah dari kamus.</div>
    @endif
    @if($tugas->sopLangkah?->sop)
        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4 text-sm">
            Berdasarkan <a class="text-blue-800 font-semibold underline" href="{{ route('sop.show', $tugas->sopLangkah->sop) }}">SOP {{ $tugas->sopLangkah->sop->kode }} — {{ $tugas->sopLangkah->sop->nama }}</a>
            · Langkah {{ $tugas->sopLangkah->urutan }} (versi SOP saat ini: {{ $tugas->sopLangkah->sop->versi }}).
            Rincian pekerjaan disimpan sebagai salinan saat dibuat, sehingga dapat disesuaikan dengan kebutuhan.
        </div>
    @endif
    @if($tugas->sopPelaksanaan)<a href="{{ route('sop.pelaksanaan',$tugas->sopPelaksanaan) }}" class="block text-sm text-blue-700 underline">Lihat rangkaian pekerjaan SOP: {{ $tugas->sopPelaksanaan->nama }}</a>@endif
    @if($tugas->ditugaskan_ke === auth()->id() || auth()->user()->isKepalaBps() || auth()->user()->isAdmin())
        <form action="{{ route('pekerjaan.update',$tugas) }}" enctype="multipart/form-data" method="POST" class="bg-white p-5 border rounded-xl space-y-3">@csrf @method('PATCH')<h2 class="font-bold">Perbarui progres & hasil pekerjaan</h2>
            <div><label class="block text-sm mb-1">Status</label><select class="rounded-lg border-slate-300 w-full" name="status">@foreach(['belum_dikerjakan'=>'Belum dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'] as $k=>$v)<option value="{{ $k }}" @selected(old('status',$tugas->status)===$k)>{{ $v }}</option>@endforeach</select></div>
            <div><label class="block text-sm mb-1">Realisasi output</label><textarea name="output_realisasi" rows="3" class="w-full rounded-lg border-slate-300">{{ old('output_realisasi',$tugas->output_realisasi) }}</textarea></div>
            <div><label class="block text-sm mb-1">File output (maks. 10 MB)</label><input type="file" name="file_output"></div>
            <p class="text-xs text-slate-500">Pekerjaan dari output lain harus menunggu sumber selesai; penyelesaian induk menunggu semua disposisi selesai.</p><button class="bg-[#1F3864] text-white px-4 py-2 text-sm rounded-lg">Simpan progres</button>
        </form>
    @endif
    <section class="bg-white border p-5 rounded-xl"><h2 class="font-bold mb-3">Riwayat disposisi bawahan ({{ $tugas->disposisi->filter(fn($anak) => \App\Support\AksesPekerjaan::dapatLihat(auth()->user(),$anak))->count() }})</h2><div class="space-y-2">@forelse($tugas->disposisi->filter(fn($anak) => \App\Support\AksesPekerjaan::dapatLihat(auth()->user(),$anak)) as $anak)<div class="flex justify-between flex-wrap gap-3 border-b pb-2 text-sm"><div>{{ $anak->judul }} <span class="text-slate-500">— {{ $anak->penerimaTugas?->name }} / {{ $anak->status }}</span></div>@if(\App\Support\AksesPekerjaan::dapatLihat(auth()->user(),$anak))<a class="text-blue-700" href="{{ route('pekerjaan.show',$anak) }}">Detail →</a>@endif</div>@empty<p class="text-sm text-slate-500">Belum ada disposisi turunan.</p>@endforelse</div></section>
    @if($pemakaiOutput->isNotEmpty())<section class="bg-white border p-5 rounded-xl"><h2 class="font-bold mb-2">Output digunakan oleh pekerjaan lain</h2>@foreach($pemakaiOutput as $pemakai)@if(\App\Support\AksesPekerjaan::dapatLihat(auth()->user(),$pemakai))<a href="{{ route('pekerjaan.show',$pemakai) }}" class="block text-blue-700 text-sm py-1">{{ $pemakai->judul }}</a>@endif @endforeach</section>@endif
</div>
</x-app-layout>
