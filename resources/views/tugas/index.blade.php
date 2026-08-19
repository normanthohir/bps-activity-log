{{-- resources/views/tugas/index.blade.php --}}
@php
    $isKepalaBps = auth()->user()->isKepalaBps();
    $createRoute = $isKepalaBps ? 'kepala-bps.tugas.create' : 'kabag.tugas.create';
    $createRouteFallback = $isKepalaBps ? 'kepala-bps.tugas.index' : 'kabag.tugas.index';
@endphp

<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Daftar Tugas</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if ($isKepalaBps)
                        Semua tugas yang diberikan ke staf di seluruh bagian.
                    @else
                        Tugas yang Anda berikan ke staf di bagian Anda.
                    @endif
                </p>
            </div>
            <a href="{{ route($createRoute) }}"
               class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Beri Tugas
            </a>
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($tugas->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada tugas</p>
                    <p class="text-sm text-gray-500 mt-1">Buat tugas baru untuk diberikan ke staf.</p>
                    <a href="{{ route($createRoute) }}"
                       class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        + Beri Tugas
                    </a>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Judul</th>
                            <th class="py-3 px-5 font-medium">Ditugaskan Ke</th>
                            @if ($isKepalaBps)
                                <th class="py-3 px-5 font-medium">Bagian</th>
                            @endif
                            <th class="py-3 px-5 font-medium">Tenggat</th>
                            <th class="py-3 px-5 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($tugas as $item)
                            @php
                                $statusStyle = match($item->status) {
                                    'selesai' => 'bg-green-50 text-green-700',
                                    'sedang_dikerjakan' => 'bg-blue-50 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $statusLabel = match($item->status) {
                                    'selesai' => 'Selesai',
                                    'sedang_dikerjakan' => 'Sedang Dikerjakan',
                                    default => 'Belum Dikerjakan',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5">
                                    <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                    @if ($item->deskripsi)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->penerimaTugas->name ?? '?', 0, 1)) }}
                                        </span>
                                        <span class="text-gray-700">{{ $item->penerimaTugas->name ?? '—' }}</span>
                                    </div>
                                </td>
                                @if ($isKepalaBps)
                                    <td class="py-3.5 px-5 text-gray-500 text-xs">{{ $item->bagian->nama_bagian ?? '—' }}</td>
                                @endif
                                <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">
                                    {{ $item->tenggat ? $item->tenggat->format('d M Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $statusStyle }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $tugas->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
