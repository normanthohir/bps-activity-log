{{-- resources/views/tugas/index.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <p class="font-medium text-lg">Tugas Tim</p>
            <a href="{{ route('kabag.tugas.create') }}" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Beri tugas
            </a>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Judul tugas</th>
                        <th class="pb-2">Ditugaskan ke</th>
                        <th class="pb-2">Tenggat</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tugas as $item)
                        <tr class="border-b">
                            <td class="py-3">{{ $item->judul }}</td>
                            <td class="py-3">{{ $item->penerimaTugas->name }}</td>
                            <td class="py-3">{{ $item->tenggat?->format('d M Y') ?? '-' }}</td>
                            <td class="py-3">
                                @php
                                    $warna = match ($item->status) {
                                        'selesai' => 'bg-green-100 text-green-700',
                                        'dikerjakan' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="{{ $warna }} text-xs px-2 py-0.5 rounded-full">
                                    {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <a href="" class="text-sm px-3 py-1.5 rounded-lg border">Lihat</a>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">Belum ada tugas yang diberikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $tugas->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
