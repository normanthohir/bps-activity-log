{{-- resources/views/approval/index-kepala-bps.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <p class="font-medium text-lg mb-4">Persetujuan Laporan — Semua Bagian</p>

        <form method="GET" action="{{ route('kepala-bps.approval.index') }}"
            class="bg-white rounded-xl border p-4 mb-4 grid grid-cols-2 md:grid-cols-5 gap-3 items-end">

            <div>
                <label class="text-xs text-gray-500 block mb-1">Bagian</label>
                <select name="bagian" class="w-full text-sm rounded-lg border-gray-300">
                    <option value="">Semua bagian</option>
                    @foreach ($daftarBagian as $b)
                        <option value="{{ $b->id }}" @selected(request('bagian') == $b->id)>{{ $b->nama_bagian }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Status</label>
                <select name="status" class="w-full text-sm rounded-lg border-gray-300">
                    <option value="">Semua status</option>
                    <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                    <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal spesifik</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="w-full text-sm rounded-lg border-gray-300">
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Bulan</label>
                <select name="bulan" class="w-full text-sm rounded-lg border-gray-300">
                    <option value="">Semua bulan</option>
                    @foreach (['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April', '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $angka => $nama)
                        <option value="{{ $angka }}" @selected(request('bulan') == $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="text-xs text-gray-500 block mb-1">Tahun</label>
                    <select name="tahun" class="w-full text-sm rounded-lg border-gray-300">
                        <option value="">Semua tahun</option>
                        @for ($t = now()->year; $t >= now()->year - 3; $t--)
                            <option value="{{ $t }}" @selected(request('tahun') == $t)>{{ $t }}
                            </option>
                        @endfor
                    </select>
                </div>

            </div>
            <div class="flex gap-1">
                <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-gray-900 text-white whitespace-nowrap">
                    Terapkan
                </button>
                <a href="{{ route('kepala-bps.approval.index') }}"
                    class="text-sm px-3 py-2 rounded-lg border bg-blue-500 text-white whitespace-nowrap">Reset</a>
            </div>
        </form>

        <div class="bg-white rounded-xl border p-5">
            @forelse ($laporan as $item)
                <a href="{{ route('kepala-bps.approval.show', $item) }}" class="hover:underline">
                    <div class="border-b first:border-t-0 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm">
                                {{ $item->user->name }}
                                <span class="text-gray-400">· {{ $item->user->bagian->nama_bagian ?? '-' }}</span>
                            </p>
                            <p class="text-sm text-gray-600">{{ Str::limit($item->uraian, 45) }}</p>
                            <p class="text-xs text-gray-400">{{ $item->tanggal->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-3">

                            <x-status-badge :status="$item->status" />
                </a>
                @if ($item->status === 'menunggu')
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('kepala-bps.approval.proses', $item) }}">
                            @csrf
                            <input type="hidden" name="aksi" value="disetujui">
                            <button class="text-sm px-3 py-1.5 rounded-lg border">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('kepala-bps.approval.proses', $item) }}">
                            @csrf
                            <input type="hidden" name="aksi" value="ditolak">
                            <button class="text-sm px-3 py-1.5 rounded-lg border text-red-600">Tolak</button>
                        </form>
                    </div>
                @endif

        </div>
    </div>
@empty
    <p class="text-sm text-gray-400 py-4">Tidak ada laporan yang cocok dengan filter ini.</p>
    @endforelse

    <div class="mt-4">
        {{ $laporan->links() }}
    </div>
    </div>

    </div>
</x-app-layout>
