<x-app-layout>
<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 space-y-5">
    <div class="flex flex-wrap justify-between gap-4 items-center">
        <div><h1 class="text-2xl font-bold text-slate-900">Pekerjaan & disposisi</h1><p class="text-sm text-slate-500">Penugasan berjenjang dengan jejak input, proses, dan output.</p></div>
        @if(\App\Support\AksesPekerjaan::bolehDisposisi(auth()->user()))
            @if(auth()->user()->isKepalaBps() || auth()->user()->isAdmin())
                <a href="{{ route('sop.index') }}" class="border border-blue-800 text-blue-900 bg-white rounded-lg px-4 py-2 text-sm font-semibold">Delegasikan SOP ke Ketua Tim</a>
            @endif
            <a href="{{ route('pekerjaan.create') }}" class="bg-[#1F3864] text-white rounded-lg px-4 py-2 text-sm font-semibold">+ Pekerjaan / disposisi baru</a>
        @endif
    </div>
    <form class="bg-white border rounded-xl p-4 flex flex-wrap gap-3" method="GET">
        <input name="cari" value="{{ request('cari') }}" placeholder="Cari judul pekerjaan" class="flex-1 min-w-48 rounded-lg border-slate-300 text-sm">
        <select name="status" class="rounded-lg border-slate-300 text-sm"><option value="">Semua status</option>
            @foreach(['belum_dikerjakan'=>'Belum dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'] as $v=>$s)<option value="{{ $v }}" @selected(request('status')===$v)>{{ $s }}</option>@endforeach
        </select>
        <button class="rounded-lg bg-slate-800 text-white px-4 py-2 text-sm">Filter</button>
    </form>
    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm text-left"><thead class="bg-slate-50 text-slate-600"><tr><th class="p-3">Pekerjaan / Disposisi</th><th class="p-3">Tim</th><th class="p-3">Pelaksana</th><th class="p-3">Tenggat</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead><tbody class="divide-y">
        @forelse($tugas as $item)
        <tr><td class="p-3"><div class="font-medium">{{ $item->judul }}</div><div class="text-xs text-slate-500">{{ $item->induk ? '↳ Disposisi dari: '.$item->induk->judul : 'Pekerjaan utama' }}</div></td>
        <td class="p-3">{{ $item->bagian?->nama_bagian }}</td><td class="p-3">{{ $item->penerimaTugas?->name }}</td>
        <td class="p-3 whitespace-nowrap">{{ $item->tenggat?->format('d/m/Y') ?? '—' }}</td>
        <td class="p-3">{{ ucwords(str_replace('_',' ',$item->status)) }}</td>
        <td class="p-3"><a class="text-blue-700 font-semibold" href="{{ route('pekerjaan.show',$item) }}">Detail →</a></td></tr>
        @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada pekerjaan yang dapat dilihat.</td></tr>@endforelse
        </tbody></table>
    </div>{{ $tugas->links() }}
</div>
</x-app-layout>
