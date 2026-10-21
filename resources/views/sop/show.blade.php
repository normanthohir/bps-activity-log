<x-app-layout>
<div class="max-w-5xl mx-auto py-8 px-4 space-y-5">
<a class="text-sm text-blue-700" href="{{ route('sop.index') }}">← Semua SOP</a>
<div class="flex justify-between flex-wrap gap-4"><div><h1 class="text-2xl font-bold">{{ $sop->kode }} · {{ $sop->nama }}</h1><p class="text-slate-500 text-sm">Versi {{ $sop->versi }} · {{ $sop->keterangan }}</p><p class="text-sm mt-2">{{ $sop->bagian ? 'SOP Tim '.$sop->bagian->kode_bagian.' — '.$sop->bagian->nama_bagian : 'SOP Kantor (Admin)' }} · Dibuat oleh {{ $sop->pembuat?->name }}</p></div>
<div class="flex flex-wrap gap-2">
@if(auth()->user()->isKepalaBps() && !$sop->bagian_id)<a class="bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm" href="{{ route('sop.delegasi.buat', $sop) }}">Delegasikan SOP ke Ketua Tim</a>@endif
@if(auth()->user()->isAdmin() || ((int)$sop->dibuat_oleh === (int)auth()->id() && \App\Support\ModeKerja::ketuaAktif(auth()->user())))
    <a href="{{ route('sop.edit',$sop) }}" class="px-4 py-2 border rounded-lg text-sm">Edit SOP</a>
@endif
</div></div>
@if($bolehMulai)
<form method="POST" action="{{ route('sop.mulai-tim', $sop) }}" class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">@csrf
    <h2 class="font-bold">Mulai SOP untuk Tim {{ $sop->bagian->kode_bagian }} — {{ $sop->bagian->nama_bagian }}</h2>
    <p class="text-xs text-slate-600">Anda adalah Ketua Tim pembuat SOP. Buat satu pelaksanaan, lalu delegasikan setiap langkah kepada pegawai tim (termasuk diri sendiri) dari menu Beri Tugas.</p>
    <div class="grid md:grid-cols-2 gap-3"><div><label class="block text-sm mb-1">Judul pelaksanaan</label><input required name="nama" value="{{ old('nama','Pelaksanaan '.$sop->nama) }}" class="w-full rounded-lg border-slate-300"></div><div><label class="block text-sm mb-1">Mulai</label><input required type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai',now()->toDateString()) }}" class="w-full rounded-lg border-slate-300"></div></div>
    <button class="bg-[#1F3864] text-white px-4 py-2 rounded-lg">Mulai & Beri Tugas Langkah 1 →</button>
</form>
@endif
@if($pelaksanaanDiterima->isNotEmpty())
<section class="bg-white border rounded-xl p-4 space-y-3"><h2 class="font-bold text-blue-900">SOP yang sedang saya kelola sebagai Ketua Tim</h2>
@foreach($pelaksanaanDiterima as $p)
    <div class="flex items-center flex-wrap justify-between gap-3 border-t pt-3"><div><strong>{{ $p->nama }}</strong><p class="text-xs text-slate-500">Tim {{ $p->tugasInduk?->bagian?->kode_bagian }} · {{ $p->tugasInduk?->status }}</p></div>
        @if(\App\Support\ModeKerja::ketuaAktif(auth()->user()) && $p->tugasInduk?->status !== 'selesai')
            <a class="bg-[#1F3864] text-white px-4 py-2 rounded-lg text-sm" href="{{ route('sop.delegasi.langkah',$p) }}">Beri Tugas per langkah →</a>
        @else<a class="text-blue-700 text-sm" href="{{ route('sop.pelaksanaan',$p) }}">Lihat pelaksanaan</a>@endif
    </div>
@endforeach</section>
@endif
@if($errors->any())<div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
<div class="space-y-4">@foreach($sop->langkah as $l)
<div class="bg-white p-5 border rounded-xl"><h2 class="font-bold text-blue-900 mb-3">Langkah {{ $l->urutan }}</h2>
<div class="grid md:grid-cols-3 gap-4 text-sm"><div><h3 class="font-semibold">INPUT</h3><p class="text-xs text-slate-500">{{ $l->sumber_input }}</p><p class="whitespace-pre-wrap">{{ $l->input }}</p></div><div><h3 class="font-semibold">PROSES</h3><p class="whitespace-pre-wrap">{{ $l->proses }}</p></div><div><h3 class="font-semibold">OUTPUT</h3><p class="whitespace-pre-wrap">{{ $l->output }}</p><p class="text-xs text-slate-500 mt-2">Durasi {{ $l->durasi_hari }} hari</p></div></div>
@if($pelaksanaanDiterima->isNotEmpty() && \App\Support\ModeKerja::ketuaAktif(auth()->user()))
<div class="mt-3 pt-3 border-t space-y-1"><div class="text-xs font-medium text-slate-500">Beri Tugas untuk langkah ini:</div>
@foreach($pelaksanaanDiterima as $p) @if($p->tugasInduk?->status !== 'selesai')
    <a class="block text-blue-700 text-sm underline" href="{{ route('pekerjaan.create', ['pelaksanaan' => $p->id, 'langkah' => $l->id]) }}">{{ $p->nama }} → Beri Tugas</a>
@endif @endforeach
</div>@endif
</div>@endforeach</div>
</div></x-app-layout>
