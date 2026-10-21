<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8 space-y-5">
    <a href="{{ route('pekerjaan.show', $induk) }}" class="text-sm text-blue-700">← Kembali ke pekerjaan induk</a>
    <div>
        <h1 class="text-2xl font-bold">Disposisi langkah SOP</h1>
        <p class="text-sm text-slate-500 mt-1">
            {{ $pelaksanaan->sop->kode }} · {{ $pelaksanaan->nama }} ·
            Tim: <strong>{{ $induk->bagian->kode_bagian ?? '' }} — {{ $induk->bagian->nama_bagian ?? '' }}</strong>
        </p>
    </div>
    <div class="bg-blue-50 border border-blue-200 text-blue-900 p-4 rounded-xl text-sm">
        <strong>Satu langkah boleh dikerjakan satu orang atau beberapa orang sekaligus, termasuk <strong>Saya sendiri</strong> sebagai Ketua Tim.</strong>
        Setiap orang menerima pekerjaan tersendiri dan melaporkan progres/output masing-masing.
        Apabila input langkah selanjutnya berasal dari output langkah sebelumnya, <strong>semua pelaksana langkah sebelumnya</strong>
        harus menyelesaikan pekerjaan terlebih dahulu. Untuk menjaga alurnya, tambahkan pelaksana sebelum langkah berikutnya didisposisikan.
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @php
        $jumlahDisposisi = $tugasLangkah->count();
        $jumlahSelesai = $tugasLangkah->filter(fn ($kumpulan) => $kumpulan->isNotEmpty() && $kumpulan->every(fn ($t) => $t->status === 'selesai'))->count();
        $jumlahPekerjaan = $tugasLangkah->flatten(1)->count();
    @endphp
    <div class="grid sm:grid-cols-4 gap-3">
        <div class="bg-white border p-4 rounded-xl"><div class="text-xs text-slate-500">Total langkah</div><div class="text-2xl font-semibold">{{ $pelaksanaan->sop->langkah->count() }}</div></div>
        <div class="bg-white border p-4 rounded-xl"><div class="text-xs text-slate-500">Langkah dibagikan</div><div class="text-2xl font-semibold">{{ $jumlahDisposisi }}</div></div>
        <div class="bg-white border p-4 rounded-xl"><div class="text-xs text-slate-500">Penugasan pegawai</div><div class="text-2xl font-semibold">{{ $jumlahPekerjaan }}</div></div>
        <div class="bg-white border p-4 rounded-xl"><div class="text-xs text-slate-500">Langkah selesai semua</div><div class="text-2xl font-semibold">{{ $jumlahSelesai }}</div></div>
    </div>

    @foreach($pelaksanaan->sop->langkah as $l)
        @php
            $existing = $tugasLangkah->get($l->id, collect());
            $previous = $pelaksanaan->sop->langkah->where('urutan', '<', $l->urutan)->sortByDesc('urutan')->first();
            $next = $pelaksanaan->sop->langkah->where('urutan', '>', $l->urutan)->sortBy('urutan')->first();
            $waiting = $l->sumber_input === 'sebelumnya' && $previous && !$tugasLangkah->has($previous->id);
            $locked = $next && $next->sumber_input === 'sebelumnya' && $tugasLangkah->has($next->id);
            $available = $anggota->whereNotIn('id', $existing->pluck('ditugaskan_ke')->all());
            $isOldForm = (string) old('sop_langkah_id') === (string) $l->id;
            $start = ($induk->tanggal_mulai ?? now())->toDateString();
            $defaultTenggat = \Illuminate\Support\Carbon::parse($start)->addDays($l->durasi_hari)->toDateString();
        @endphp
        <section class="bg-white border p-5 rounded-xl space-y-4">
            <div class="flex flex-wrap gap-3 justify-between items-center">
                <h2 class="font-bold text-blue-900">Langkah {{ $l->urutan }} · {{ \Illuminate\Support\Str::limit($l->proses, 120) }}</h2>
                @if($existing->isNotEmpty() && $existing->every(fn($t) => $t->status === 'selesai'))
                    <span class="text-xs font-semibold rounded-lg px-2 py-1 bg-green-100 text-green-800">Selesai ({{ $existing->count() }} pelaksana)</span>
                @elseif($existing->isNotEmpty())
                    <span class="text-xs font-semibold rounded-lg px-2 py-1 bg-blue-100 text-blue-800">{{ $existing->where('status', 'selesai')->count() }}/{{ $existing->count() }} pelaksana selesai</span>
                @elseif($waiting)
                    <span class="text-xs font-semibold rounded-lg px-2 py-1 bg-amber-100 text-amber-800">Menunggu disposisi sebelumnya</span>
                @else
                    <span class="text-xs font-semibold rounded-lg px-2 py-1 bg-slate-100 text-slate-700">Belum dibagikan</span>
                @endif
            </div>
            <div class="grid md:grid-cols-3 gap-3 text-sm">
                <div><div class="font-semibold text-slate-500">INPUT</div><p class="whitespace-pre-wrap">{{ $l->input ?: '—' }}</p><div class="text-xs text-slate-500">Sumber: {{ $l->sumber_input }}</div></div>
                <div><div class="font-semibold text-slate-500">PROSES</div><p class="whitespace-pre-wrap">{{ $l->proses }}</p></div>
                <div><div class="font-semibold text-slate-500">OUTPUT</div><p class="whitespace-pre-wrap">{{ $l->output ?: '—' }}</p><div class="text-xs text-slate-500">Durasi acuan: {{ $l->durasi_hari }} hari</div></div>
            </div>

            @if($existing->isNotEmpty())
                <div class="bg-slate-50 border rounded-lg p-3 text-sm space-y-2">
                    <div class="font-semibold">Pelaksana langkah ini ({{ $existing->count() }})</div>
                    @foreach($existing as $pekerjaan)
                        <div class="flex flex-wrap gap-2 justify-between items-center border-t pt-2">
                            <div>
                                <strong>{{ $pekerjaan->penerimaTugas?->name }}</strong>
                                @if((int) $pekerjaan->ditugaskan_ke === (int) $induk->ditugaskan_ke)
                                    <span class="text-xs text-blue-700">(Ketua Tim / saya sendiri)</span>
                                @endif
                                <span class="text-slate-500"> · {{ ucwords(str_replace('_', ' ', $pekerjaan->status)) }}</span>
                            </div>
                            <a class="underline text-blue-700" href="{{ route('pekerjaan.show', $pekerjaan) }}">Lihat pekerjaan →</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($waiting)
                <p class="text-sm text-amber-700">Disposisikan langkah {{ $previous->urutan }} terlebih dahulu agar output seluruh pelaksananya menjadi input langkah ini.</p>
            @elseif($induk->status === 'selesai')
                <p class="text-sm text-slate-500">Tugas induk sudah selesai.</p>
            @elseif($locked)
                <p class="text-sm text-slate-500">Penambahan pelaksana ditutup karena langkah berikutnya sudah dibagikan.</p>
            @elseif($available->isEmpty())
                <p class="text-sm text-slate-500">Semua pegawai yang memenuhi syarat sudah mendapat tugas pada langkah ini.</p>
            @else
                <a class="inline-flex px-5 py-2 bg-[#1F3864] text-white text-sm rounded-lg"
                    href="{{ route('pekerjaan.create', ['pelaksanaan' => $pelaksanaan->id, 'langkah' => $l->id]) }}">
                    {{ $existing->isEmpty() ? 'Beri Tugas dari SOP →' : 'Tambah pelaksana melalui Beri Tugas →' }}
                </a>
            @endif
        </section>
    @endforeach
    <div class="flex gap-3"><a href="{{ route('pekerjaan.show', $induk) }}" class="border rounded-lg px-4 py-2 text-sm">Kembali ke tugas induk</a><a href="{{ route('sop.pelaksanaan', $pelaksanaan) }}" class="text-blue-700 underline text-sm self-center">Lihat rangkaian SOP →</a></div>
</div>
</x-app-layout>
