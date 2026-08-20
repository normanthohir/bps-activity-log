{{-- resources/views/laporan/index.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <p class="font-medium text-lg">Riwayat Laporan Saya</p>
            <a href="{{ route('laporan.create') }}" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Laporan baru
            </a>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Uraian</th>
                        <th class="pb-2">Lokasi</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporan as $item)
                        <tr class="border-b">
                            <td class="py-3">{{ $item->tanggal->format('d M Y') }}</td>
                            <td class="py-3">{{ Str::limit($item->uraian, 45) }}</td>
                            <td class="py-3 capitalize">{{ str_replace('_', ' ', $item->lokasi) }}</td>
                            <td class="py-3"><x-status-badge :status="$item->status" /></td>
                            <td class="py-3 text-right">
                                @if (in_array($item->status, ['draft', 'dikembalikan']))
                                    <a href="{{ route('laporan.edit', $item) }}" class="underline text-sm">
                                        {{ $item->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                                    </a>
                                @else
                                    <span class="text-gray-300 text-sm">
                                        <a href="" class="">
                                            Lihat
                                        </a>
                                    </span>
                                @endif
                            </td>
                        </tr>

                        @if ($item->status === 'dikembalikan' && $item->logApproval->last())
                            <tr class="bg-red-50">
                                <td colspan="5" class="px-3 py-2 text-xs text-red-700">
                                    Catatan penolakan: {{ $item->logApproval->last()->catatan ?? '-' }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $laporan->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
