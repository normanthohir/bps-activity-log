<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Tugas Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar tugas yang diberikan oleh atasan kepada Anda.</p>
        </div>

        {{-- Filter by status --}}
        <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
            <select name="status" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Status</option>
                <option value="belum_dikerjakan" @selected(request('status') === 'belum_dikerjakan')>Belum Dikerjakan</option>
                <option value="sedang_dikerjakan" @selected(request('status') === 'sedang_dikerjakan')>Sedang Dikerjakan</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>
        </form>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($tugas->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <p class="text-gray-500 text-sm">Belum ada tugas yang diberikan kepada Anda.</p>
                </div>
            @else
                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Judul Tugas</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Dari</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tenggat</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($tugas as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                        @if ($item->deskripsi)
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $item->deskripsi }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700">
                                        {{ $item->pemberiTugas->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700">
                                        @if ($item->tenggat)
                                            @php
                                                $isOverdue = \Carbon\Carbon::parse($item->tenggat)->isPast() && $item->status !== 'selesai';
                                            @endphp
                                            <span class="{{ $isOverdue ? 'text-red-600 font-medium' : '' }}">
                                                {{ \Carbon\Carbon::parse($item->tenggat)->translatedFormat('d M Y') }}
                                            </span>
                                            @if ($isOverdue)
                                                <span class="text-xs text-red-500 block">Terlambat</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($item->status === 'selesai')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                Selesai
                                            </span>
                                        @elseif ($item->status === 'sedang_dikerjakan')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Sedang Dikerjakan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Belum Dikerjakan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($item->status !== 'selesai')
                                            <form method="POST" action="{{ route('staf.tugas.update', $item) }}" class="flex items-center gap-2">
                                                @csrf @method('PATCH')
                                                <select name="status" onchange="this.form.submit()"
                                                        class="text-xs border border-gray-300 rounded-lg py-1.5 pl-2 pr-6 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                                                    <option value="belum_dikerjakan" @selected($item->status === 'belum_dikerjakan')>Belum Dikerjakan</option>
                                                    <option value="sedang_dikerjakan" @selected($item->status === 'sedang_dikerjakan')>Sedang Dikerjakan</option>
                                                    <option value="selesai" @selected($item->status === 'selesai')>Selesai</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @foreach ($tugas as $item)
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <p class="font-medium text-gray-900 text-sm">{{ $item->judul }}</p>
                                @if ($item->status === 'selesai')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Selesai
                                    </span>
                                @elseif ($item->status === 'sedang_dikerjakan')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Dikerjakan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Belum
                                    </span>
                                @endif
                            </div>
                            @if ($item->deskripsi)
                                <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $item->deskripsi }}</p>
                            @endif
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                <span>Dari: {{ $item->pemberiTugas->name ?? '-' }}</span>
                                @if ($item->tenggat)
                                    @php $isOverdue = \Carbon\Carbon::parse($item->tenggat)->isPast() && $item->status !== 'selesai'; @endphp
                                    <span class="{{ $isOverdue ? 'text-red-600 font-medium' : '' }}">
                                        Tenggat: {{ \Carbon\Carbon::parse($item->tenggat)->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            @if ($item->status !== 'selesai')
                                <form method="POST" action="{{ route('staf.tugas.update', $item) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="text-xs border border-gray-300 rounded-lg py-1.5 pl-2 pr-6 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                                        <option value="belum_dikerjakan" @selected($item->status === 'belum_dikerjakan')>Belum Dikerjakan</option>
                                        <option value="sedang_dikerjakan" @selected($item->status === 'sedang_dikerjakan')>Sedang Dikerjakan</option>
                                        <option value="selesai" @selected($item->status === 'selesai')>Selesai</option>
                                    </select>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($tugas->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $tugas->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>
