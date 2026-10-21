<x-app-layout><div class="max-w-2xl mx-auto px-4 py-8"><h1 class="text-2xl font-bold mb-5">Edit Kamus Kerja</h1>
@if($errors->any())<div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded mb-3">{{ implode('; ', $errors->all()) }}</div>@endif
<form method="POST" action="{{ route('kamus.update', $kamus) }}" class="p-6 bg-white border rounded-xl space-y-4">@csrf @method('PATCH')
<div class="text-sm text-slate-500">Lingkup: {{ $kamus->bagian ? $kamus->bagian->kode_bagian.' — '.$kamus->bagian->nama_bagian : 'Kantor' }}</div>
<div><label class="block text-sm mb-1 font-medium">Nama *</label><input required maxlength="255" name="nama" value="{{ old('nama', $kamus->nama) }}" class="w-full rounded-lg border-slate-300"></div>
@foreach(['input'=>'Input', 'proses'=>'Proses', 'output'=>'Output'] as $kolom=>$label)
<div><label class="block text-sm mb-1">{{ $label }} (opsional)</label><textarea rows="3" name="{{ $kolom }}" class="w-full rounded-lg border-slate-300">{{ old($kolom, $kamus->$kolom) }}</textarea></div>
@endforeach
<div class="flex justify-end gap-3"><a href="{{ route('kamus.index') }}" class="px-4 py-2 border rounded-lg">Batal</a><button class="px-4 py-2 rounded-lg bg-[#1F3864] text-white">Simpan Perubahan</button></div>
</form></div></x-app-layout>
