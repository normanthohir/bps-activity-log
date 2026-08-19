{{-- resources/views/tugas/index-staf.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        <p class="font-medium text-lg mb-4">Tugas Aktif</p>

        <div class="bg-white rounded-xl border p-5 mb-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Judul</th>
                        <th class="pb-2">Diberikan oleh</th>
                        <th class="pb-2">Tenggat</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tugasAktif as $tugas)
                        @php
                            $warna = match($tugas->status) {
                                'dikerjakan' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="border-b">
                            <td class="py-3">{{ $tugas->judul }}</td>
                            <td class="py-3">
                                {{ $tugas->pemberiTugas->name }}
                                <span class="text-xs text-gray-400">
                                    ({{ $tugas->pemberiTugas->isKepalaBps() ? 'Kepala BPS' : 'Kepala Bagian' }})
                                </span>
                            </td>
                            <td class="py-3">{{ $tugas->tenggat?->format('d M Y') ?? '-' }}</td>
                            <td class="py-3">
                                <span class="{{ $warna }} text-xs px-2 py-0.5 rounded-full">
                                    {{ ucwords(str_replace('_', ' ', $tugas->status)) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('tugas.show', $tugas) }}" class="underline">Detail</a>
                                <a href="{{ route('laporan.create', ['tugas' => $tugas->id]) }}" class="underline ml-2">
                                    Buat laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Tidak ada tugas aktif saat ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tugasSelesai->count())
            <p class="font-medium text-sm text-gray-500 mb-3">Tugas selesai</p>
            <div class="bg-white rounded-xl border p-5">
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($tugasSelesai as $tugas)
                            <tr class="border-b last:border-b-0">
                                <td class="py-2">{{ $tugas->judul }}</td>
                                <td class="py-2 text-right">
                                    <a href="{{ route('tugas.show', $tugas) }}" class="underline text-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $tugasSelesai->links() }}
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
