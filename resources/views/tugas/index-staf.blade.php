{{-- resources/views/tugas/index-staf.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Tugas Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar tugas yang ditugaskan kepada Anda.</p>
        </div>

        {{-- Tugas Aktif --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Tugas Aktif</h2>
            </div>

            @if ($tugasAktif->isEmpty())
                <div class="text-center py-12 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada tugas aktif</p>
                    <p class="text-sm text-gray-500 mt-1">Saat ini Anda belum memiliki tugas.</p>
                </div>
            @else
                <div class="overflow-x-auto pb-16">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Judul</th>
                                <th class="py-3 px-5 font-medium">Diberikan Oleh</th>
                                <th class="py-3 px-5 font-medium">Tenggat</th>
                                <th class="py-3 px-5 font-medium">Status</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tugasAktif as $tugas)
                                @php
                                    $warna = match ($tugas->status) {
                                        'dikerjakan' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <p class="font-medium text-gray-900">{{ $tugas->judul }}</p>
                                        @if ($tugas->deskripsi)
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ Str::limit($tugas->deskripsi, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-700">{{ $tugas->pemberiTugas->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">
                                        {{ $tugas->tenggat ? $tugas->tenggat->format('d M Y') : '—' }}
                                    </td>

                                    <td class="py-3.5 px-5">
                                        {{-- <span
                                            class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $statusStyle }}">
                                            {{ $statusLabel }}
                                        </span> --}}
                                        <span class="{{ $warna }} text-xs px-2 py-0.5 rounded-full">
                                            {{ ucwords(str_replace('_', ' ', $tugas->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <x-dropdown-action>
                                            @if (auth()->user()->role === 'staf')
                                                <a href="{{ route('staf.tugas.show', $tugas) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Lihat
                                                </a>
                                            @endif
                                            @if (auth()->user()->role === 'kepala_bagian')
                                                <a href="{{ route('kabag.tugas-aktif.show', $tugas) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Lihat
                                                </a>
                                            @endif
                                            @if (auth()->user()->role === 'kepala_bagian')
                                                @if ($tugas->status != 'dikerjakan')
                                                    <a href="{{ route('kabag.laporan.create', ['tugas' => $tugas->id]) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Buat laporan
                                                    </a>
                                                @endif
                                            @else
                                                @if ($tugas->status != 'dikerjakan')
                                                    <a href="{{ route('laporan.create', ['tugas' => $tugas->id]) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Buat laporan
                                                    </a>
                                                @endif
                                            @endif

                                        </x-dropdown-action>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Tugas Selesai --}}
            @if ($tugasSelesai->count())
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Tugas Selesai</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                    <th class="py-3 px-5 font-medium">Judul</th>
                                    <th class="py-3 px-5 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($tugasSelesai as $tugas)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3 px-5 text-gray-700">{{ $tugas->judul }}</td>
                                        <td class="py-3 px-5 text-right">
                                            <a href="{{ route('staf.tugas.show', $tugas) }}"
                                                class="text-[#1F3864] hover:underline text-sm font-medium">
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-3">
                        {{ $tugasSelesai->links() }}
                    </div>
                </div>
            @endif

        </div>
</x-app-layout>
