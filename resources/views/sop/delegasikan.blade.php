<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8 space-y-5">
    <a href="{{ route('sop.show',$sop) }}" class="text-sm text-blue-700">← Kembali ke SOP</a>
    <div>
        <h1 class="text-2xl font-bold">Disposisikan satu SOP utuh</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $sop->kode }} · {{ $sop->nama }} ({{ $sop->langkah->count() }} langkah). Kepala BPS cukup menunjuk satu Ketua Tim. Ketua Tim kemudian membagikan setiap langkah kepada anggotanya.</p>
    </div>
    @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('sop.delegasi.simpan',$sop) }}" class="bg-white border rounded-xl p-6 space-y-5">@csrf
        <div><label class="block font-medium text-sm mb-1" for="nama">Judul penugasan SOP *</label><input id="nama" name="nama" required maxlength="255" value="{{ old('nama','Pelaksanaan '.$sop->nama) }}" class="w-full rounded-lg border-slate-300"></div>
        <div class="grid md:grid-cols-2 gap-4">
            <div><label for="bagian_id" class="block font-medium text-sm mb-1">Tim tujuan (urut kode tim) *</label>
                <select id="bagian_id" name="bagian_id" required class="w-full rounded-lg border-slate-300"><option value="">Pilih tim</option>@foreach($tim as $item)<option value="{{ $item->id }}" @selected(old('bagian_id')==$item->id)>{{ $item->kode_bagian }} — {{ $item->nama_bagian }}</option>@endforeach</select>
            </div>
            <div><label for="ketua_id" class="block font-medium text-sm mb-1">Ketua Tim penerima *</label>
                <select id="ketua_id" name="ketua_id" required class="w-full rounded-lg border-slate-300"><option value="">Pilih Ketua Tim</option>@foreach($tim as $item)@foreach($item->ketuaTim as $ketua)<option value="{{ $ketua->id }}" data-tim-id="{{ $item->id }}" @selected(old('ketua_id')==$ketua->id && old('bagian_id')==$item->id)>{{ $ketua->name }} ({{ $item->kode_bagian }})</option>@endforeach @endforeach</select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div><label for="tanggal_mulai" class="block font-medium text-sm mb-1">Mulai *</label><input id="tanggal_mulai" name="tanggal_mulai" type="date" required value="{{ old('tanggal_mulai',now()->toDateString()) }}" class="w-full rounded-lg border-slate-300"></div>
            <div><label for="tenggat" class="block font-medium text-sm mb-1">Tenggat SOP (opsional)</label><input id="tenggat" name="tenggat" type="date" value="{{ old('tenggat') }}" class="w-full rounded-lg border-slate-300"><p class="text-xs text-slate-500">Bila kosong, dihitung dari total durasi langkah SOP.</p></div>
        </div>
        <div class="border rounded-lg p-4 space-y-3">
            <h2 class="font-semibold text-blue-900">Input awal SOP</h2>
            <div><label for="jenis_input" class="block text-sm mb-1">Jenis sumber *</label><select id="jenis_input" name="jenis_input" class="w-full rounded-lg border-slate-300"><option value="manual" @selected(old('jenis_input')==='manual')>Arahan langsung</option><option value="surat" @selected(old('jenis_input')==='surat')>Surat pusat/provinsi/instansi lain</option></select></div>
            <div><label class="block text-sm mb-1" for="uraian_input">Uraian input</label><textarea id="uraian_input" name="uraian_input" rows="3" class="w-full rounded-lg border-slate-300">{{ old('uraian_input') }}</textarea></div>
            <div class="grid md:grid-cols-3 gap-3"><input aria-label="Nomor surat" name="nomor_surat" placeholder="Nomor surat" value="{{ old('nomor_surat') }}" class="rounded-lg border-slate-300"><input aria-label="Asal surat" name="asal_surat" placeholder="Asal surat" value="{{ old('asal_surat') }}" class="rounded-lg border-slate-300"><input aria-label="Tanggal surat" name="tanggal_surat" type="date" value="{{ old('tanggal_surat') }}" class="rounded-lg border-slate-300"></div>
            <div><label class="block text-sm mb-1" for="file_input">Lampiran (maks. 10 MB)</label><input id="file_input" name="file_input" type="file" class="text-sm"></div>
        </div>
        <div><label for="output_rencana" class="block font-medium text-sm mb-1">Output akhir yang diharapkan (opsional)</label><textarea id="output_rencana" name="output_rencana" rows="3" placeholder="Bila kosong, mengikuti output langkah terakhir" class="w-full rounded-lg border-slate-300">{{ old('output_rencana') }}</textarea></div>
        <div class="border rounded-lg p-4 bg-slate-50 space-y-2"><h2 class="font-semibold text-sm">Ringkasan langkah SOP</h2>@foreach($sop->langkah as $langkah)<div class="text-sm border-t py-2"><strong>{{ $langkah->urutan }}.</strong> {{ $langkah->proses }} <span class="text-slate-500">({{ $langkah->durasi_hari }} hari)</span></div>@endforeach</div>
        <div class="flex justify-end gap-2"><a href="{{ route('sop.show',$sop) }}" class="border rounded-lg px-4 py-2">Batal</a><button class="bg-[#1F3864] text-white rounded-lg px-5 py-2 font-semibold">Kirim SOP ke Ketua Tim</button></div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tim = document.getElementById('bagian_id');
    const ketua = document.getElementById('ketua_id');
    const options = Array.from(ketua.options);
    function sinkronkanKetua() {
        options.forEach(option => {
            if (!option.value) return;
            option.hidden = option.dataset.timId !== tim.value;
            option.disabled = option.dataset.timId !== tim.value;
        });
        if (ketua.selectedOptions[0]?.disabled) ketua.value = '';
    }
    tim.addEventListener('change', () => { ketua.value = ''; sinkronkanKetua(); });
    sinkronkanKetua();
});
</script>
</x-app-layout>
