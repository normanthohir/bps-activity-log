{{-- resources/views/approval/index.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl border p-5">
            <p class="font-medium mb-4">Laporan menunggu persetujuan</p>

            @forelse ($laporan as $item)
                <div class="border-t py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm">{{ $item->user->name }} — {{ Str::limit($item->uraian, 40) }}</p>
                        <p class="text-xs text-gray-500">{{ $item->tanggal->format('d M Y') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('kabag.approval.proses', $item) }}">
                            @csrf
                            <input type="hidden" name="aksi" value="disetujui">
                            <button class="text-sm px-3 py-1.5 rounded-lg border">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('kabag.approval.proses', $item) }}">
                            @csrf
                            <input type="hidden" name="aksi" value="ditolak">
                            <button class="text-sm px-3 py-1.5 rounded-lg border text-red-600">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4">Tidak ada laporan yang menunggu persetujuan.</p>
            @endforelse

            {{ $laporan->links() }}
        </div>

    </div>
</x-app-layout>
