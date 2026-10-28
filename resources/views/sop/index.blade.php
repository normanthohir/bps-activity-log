<x-app-layout><div class="max-w-5xl mx-auto px-4 py-8 space-y-5">
<div class="flex flex-wrap justify-between items-start gap-3"><div><h1 class="text-2xl font-bold">Standar Operasional Prosedur (SOP)</h1><p class="text-sm text-slate-500">Admin mendeklarasikan SOP kantor, Kepala BPS mendelegasikannya ke Ketua Tim. Ketua Tim dapat membuat SOP untuk tim yang dipimpinnya dan membagi pekerjaan tiap langkah.</p></div>
@if(auth()->user()->isAdmin() || \App\Support\ModeKerja::ketuaAktif(auth()->user()))<a class="px-4 py-2 bg-[#1F3864] text-white rounded-lg text-sm" href="{{ route('sop.create') }}">+ Tambah SOP</a>@endif</div>
@if(auth()->user()->isAdmin() || \App\Support\ModeKerja::ketuaAktif(auth()->user()))
@include('partials.import-excel', [
    'judul' => 'SOP (rangkaian IPO)', 'jenisImport' => 'sop',
    'templateRoute' => 'sop.template-excel', 'importRoute' => 'sop.import-excel',
    'catatanImport' => 'Satu baris = satu langkah. Impor Admin menghasilkan SOP kantor. Impor Ketua Tim harus memilih tim pemilik di bawah.',
    'timPilihanImport' => $timPilihanImport,
])@endif
<div class="bg-white border rounded-xl divide-y">@forelse($sops as $sop)
<div class="p-4 flex justify-between items-center gap-4 flex-wrap"><div><a class="font-semibold text-blue-700" href="{{ route('sop.show',$sop) }}">{{ $sop->kode }} · {{ $sop->nama }}</a>
<p class="text-xs text-slate-500">{{ $sop->bagian ? 'Tim '.$sop->bagian->kode_bagian.' — '.$sop->bagian->nama_bagian : 'SOP Kantor' }} · Versi {{ $sop->versi }} · {{ $sop->langkah_count }} langkah</p></div>
<div class="flex gap-3 items-center">@if(auth()->user()->isKepalaBps() && !$sop->bagian_id)<a class="text-sm text-emerald-700 underline" href="{{ route('sop.delegasi.buat',$sop) }}">Delegasikan</a>@endif
<a class="text-sm text-blue-700" href="{{ route('sop.show',$sop) }}">Detail SOP →</a></div>
@if(\App\Support\ModeKerja::ketuaAktif(auth()->user()))
    <div class="w-full flex flex-wrap gap-2">
        @foreach($sop->pelaksanaan as $pel)
            @if($pel->tugasInduk && auth()->user()->memimpinTim($pel->tugasInduk->bagian_id))
                <a class="text-sm rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-blue-800" href="{{ route('sop.delegasi.langkah',$pel) }}">Beri Tugas: {{ $pel->nama }} →</a>
            @endif
        @endforeach
        @if($sop->bagian_id && $sop->dibuat_oleh === auth()->id() && auth()->user()->memimpinTim($sop->bagian_id))
            <a class="text-sm text-blue-700 underline px-3 py-2" href="{{ route('sop.show',$sop) }}">Mulai pelaksanaan SOP tim →</a>
        @endif
    </div>
@endif
</div>
@empty<p class="p-6 text-slate-500 text-sm">Belum ada SOP dalam lingkup akun Anda.</p>@endforelse</div>{{ $sops->links() }}</div></x-app-layout>
