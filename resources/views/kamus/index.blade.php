<x-app-layout>
<div class="max-w-6xl mx-auto px-4 py-8 space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><h1 class="text-2xl font-bold">Kamus Kerja</h1><p class="text-sm text-slate-500">Referensi judul pekerjaan yang bisa dipilih saat memberi tugas. Nama wajib; Input, Proses, Output opsional.</p></div>
        <a href="{{ route('pekerjaan.create') }}" class="px-4 py-2 rounded-lg bg-[#1F3864] text-white text-sm">+ Beri Tugas</a>
    </div>
    @if(session('success'))<div class="p-3 rounded border border-green-200 bg-green-50 text-green-800 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm whitespace-pre-line">{{ implode("\n", $errors->all()) }}</div>@endif
    <div class="grid lg:grid-cols-2 gap-4">
      <form method="POST" action="{{ route('kamus.store') }}" class="bg-white rounded-xl p-5 border space-y-3">@csrf
        <h2 class="font-semibold text-blue-900">Tambah Kamus Kerja</h2>
        <div><label class="block text-sm font-medium mb-1">Nama pekerjaan *</label><input name="nama" required maxlength="255" value="{{ old('nama') }}" class="w-full rounded-lg border-slate-300" placeholder="Contoh: Validasi data hasil survei"></div>
        @if(!auth()->user()->isAdmin())
          <div><label class="block text-sm font-medium mb-1">Tim pemilik *</label><select name="bagian_id" required class="w-full rounded-lg border-slate-300"><option value="">Pilih tim</option>@foreach($timPilihan as $t)<option value="{{ $t->id }}" @selected(old('bagian_id')==$t->id)>{{ $t->kode_bagian }} — {{ $t->nama_bagian }}</option>@endforeach</select></div>
        @else <div class="rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-900">Entri Admin adalah Kamus Kerja kantor, dapat dipakai seluruh tim.</div> @endif
        <div><label class="block text-sm mb-1">Input (opsional)</label><textarea name="input" rows="2" class="w-full rounded-lg border-slate-300">{{ old('input') }}</textarea></div>
        <div><label class="block text-sm mb-1">Proses (opsional)</label><textarea name="proses" rows="2" class="w-full rounded-lg border-slate-300">{{ old('proses') }}</textarea></div>
        <div><label class="block text-sm mb-1">Output (opsional)</label><textarea name="output" rows="2" class="w-full rounded-lg border-slate-300">{{ old('output') }}</textarea></div>
        <button class="bg-[#1F3864] text-white px-4 py-2 rounded-lg">Simpan Kamus</button>
      </form>
      <form method="POST" enctype="multipart/form-data" action="{{ route('kamus.import-excel') }}" class="bg-white rounded-xl p-5 border space-y-3">@csrf
        <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-semibold text-blue-900">Import Excel – Kamus Kerja</h2><a class="px-3 py-2 border border-blue-800 text-blue-800 rounded-lg text-sm" href="{{ route('kamus.template-excel') }}">↓ Unduh Template Excel</a></div>
        <p class="text-xs text-slate-500">Pada sheet “Kamus Kerja”, hanya nama yang wajib. Input, Proses, Output boleh kosong. File maks. 5 MB.</p>
        @if(!auth()->user()->isAdmin())
            <div><label class="block text-sm font-medium mb-1">Tim pemilik hasil impor *</label><select name="bagian_id" required class="w-full rounded-lg border-slate-300"><option value="">Pilih tim</option>@foreach($timPilihan as $t)<option value="{{ $t->id }}" @selected(old('bagian_id')==$t->id)>{{ $t->kode_bagian }} — {{ $t->nama_bagian }}</option>@endforeach</select></div>
        @endif
        <input type="file" name="file_excel" accept=".xlsx" required class="block text-sm w-full">
        <button class="bg-[#1F3864] text-white px-4 py-2 rounded-lg">Import Excel</button>
        <p class="text-xs text-slate-500">Impor tambah-data saja. Bila ada duplikat/kesalahan, seluruh file ditolak.</p>
      </form>
    </div>
    <div class="bg-white rounded-xl border overflow-x-auto">
        <div class="p-4"><form class="flex gap-2" method="GET"><input name="cari" value="{{ request('cari') }}" placeholder="Cari nama pekerjaan" class="flex-1 rounded-lg border-slate-300"><button class="px-4 py-2 rounded-lg bg-[#1F3864] text-white">Cari</button></form></div>
        <table class="min-w-full text-sm"><thead class="bg-slate-50 text-left"><tr><th class="px-4 py-3">Nama Pekerjaan</th><th>Lingkup</th><th>Input / Proses / Output</th><th>Aksi</th></tr></thead>
        <tbody class="divide-y">@forelse($kamus as $k)<tr><td class="px-4 py-3 font-semibold">{{ $k->nama }}</td><td>{{ $k->bagian ? $k->bagian->kode_bagian.' — '.$k->bagian->nama_bagian : 'Kantor' }}</td><td class="text-xs text-slate-500">{{ $k->input ? 'Input · ' : '' }}{{ $k->proses ? 'Proses · ' : '' }}{{ $k->output ? 'Output' : '' }}{{ !$k->input && !$k->proses && !$k->output ? 'Hanya nama' : '' }}</td><td class="px-4 whitespace-nowrap">
        @if(auth()->user()->isAdmin() || ($k->bagian_id && auth()->user()->memimpinTim($k->bagian_id) && (int)$k->dibuat_oleh===(int)auth()->id()))
            <a href="{{ route('kamus.edit',$k) }}" class="text-blue-700 mr-3">Edit</a><form class="inline" method="POST" action="{{ route('kamus.destroy',$k) }}" onsubmit="return confirm('Hapus entri kamus? Pekerjaan yang sudah dibuat tidak terhapus.')">@csrf @method('DELETE')<button class="text-red-700">Hapus</button></form>
        @endif
        </td></tr>@empty<tr><td colspan="4" class="text-center text-slate-500 p-6">Belum ada entri Kamus Kerja.</td></tr>@endforelse</tbody></table>
    </div>{{ $kamus->links() }}
</div>
</x-app-layout>
