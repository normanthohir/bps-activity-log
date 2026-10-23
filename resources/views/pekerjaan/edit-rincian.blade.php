<x-app-layout><div class="max-w-4xl mx-auto px-4 py-8 space-y-5">
    <a href="{{ route('pekerjaan.show', $tugas) }}" class="text-blue-700 text-sm">← Kembali ke pekerjaan</a>
    <div><h1 class="font-bold text-2xl">Edit Rincian Pekerjaan</h1><p class="text-sm text-slate-500">{{ $tugas->penerimaTugas?->name }} · {{ $tugas->bagian?->kode_bagian }} — {{ $tugas->bagian?->nama_bagian }}
        @if($tugas->sopLangkah) · SOP {{ $tugas->sopLangkah->sop->kode }}, langkah {{ $tugas->sopLangkah->urutan }} @endif</p></div>
    <p class="text-sm bg-blue-50 border border-blue-200 rounded-lg p-3">Rincian pekerjaan ini berdiri sendiri. Perubahan tidak mengubah template SOP atau rincian pekerjaan pelaksana lainnya. Identitas pelaksana dan relasi output langkah sebelumnya tetap dilindungi.</p>
    @if($errors->any())<div class="text-red-800 bg-red-50 border rounded p-3">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
    <form method="POST" action="{{ route('pekerjaan.update-rincian', $tugas) }}" class="bg-white p-5 rounded-xl border space-y-4">@csrf @method('PATCH')
        <div><label class="block text-sm mb-1">Judul pekerjaan *</label><input required name="judul" maxlength="255" value="{{ old('judul', $tugas->judul) }}" class="w-full rounded-lg border-slate-300"></div>
        <div class="grid md:grid-cols-2 gap-4"><div><label class="block text-sm mb-1">Tanggal mulai</label><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $tugas->tanggal_mulai?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300"></div><div><label class="block text-sm mb-1">Tenggat</label><input type="date" name="tenggat" value="{{ old('tenggat', $tugas->tenggat?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300"></div></div>
        <div><label class="block font-semibold text-blue-900 mb-1">INPUT ({{ $tugas->jenis_input }})</label><textarea name="uraian_input" rows="3" class="w-full rounded-lg border-slate-300">{{ old('uraian_input', $tugas->uraian_input) }}</textarea></div>
        <div><label class="block font-semibold text-blue-900 mb-1">PROSES *</label><textarea required name="deskripsi" rows="4" class="w-full rounded-lg border-slate-300">{{ old('deskripsi', $tugas->deskripsi) }}</textarea></div>
        <div><label class="block font-semibold text-blue-900 mb-1">OUTPUT RENCANA</label><textarea name="output_rencana" rows="3" class="w-full rounded-lg border-slate-300">{{ old('output_rencana', $tugas->output_rencana) }}</textarea></div>
        <div class="flex gap-3 justify-end"><a class="px-4 py-2 border rounded-lg" href="{{ route('pekerjaan.show', $tugas) }}">Batal</a><button class="px-5 py-2 bg-[#1F3864] rounded-lg text-white">Simpan perubahan</button></div>
    </form>
</div></x-app-layout>
