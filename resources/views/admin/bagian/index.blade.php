{{-- resources/views/admin/bagian/index.blade.php --}}
@php
    $adaFilter = request()->filled('search') || request()->filled('status');
@endphp

<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Kelola Tim/Seksi</h1>
                <p class="text-sm text-gray-500 mt-1">Struktur unit kerja dan penanggung jawab tiap tim.</p>
            </div>
            <a href="{{ route('admin.bagian.create') }}"
                class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Tim
            </a>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Total tim</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $ringkasan['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Total pegawai</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $ringkasan['pegawai'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Belum ada ketua tim</p>
                <p
                    class="text-2xl font-semibold {{ $ringkasan['belum_kepala'] ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                    {{ $ringkasan['belum_kepala'] }}
                </p>
            </div>
        </div>

        {{-- Pencarian dan filter --}}
        <form method="GET" action="{{ route('admin.bagian.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-3 mb-4 flex flex-col lg:flex-row gap-3 max-w-full">

            <div class="relative flex-grow min-w-">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                    placeholder="Cari nama atau kode tim..."
                    oninput="clearTimeout(window.searchTimer); window.searchTimer = setTimeout(() => this.form.submit(), 500)"
                    @if (request()->filled('search')) autofocus onfocus="this.setSelectionRange(this.value.length, this.value.length)" @endif
                    class="w-full pl-9 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
            </div>

            <div class="flex items-center gap-6 shrink-0">
                <select name="status" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864] w-full lg:w-auto">
                    <option value="">Semua tim</option>
                    <option value="ada" @selected(request('status') === 'ada')>Sudah ada ketua</option>
                    <option value="belum" @selected(request('status') === 'belum')>Belum ada ketua</option>
                </select>

                @if ($adaFilter)
                    <a href="{{ route('admin.bagian.index') }}"
                        class="text-sm text-gray-500 hover:text-gray-800 px-2 whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Hasil --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($bagian->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    @if ($adaFilter)
                        <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada tim yang cocok</p>
                        <p class="text-sm text-gray-500 mt-1">Coba ubah kata kunci atau filter yang dipakai.</p>
                    @else
                        <p class="text-sm font-medium text-gray-900 mt-3">Belum ada tim</p>
                        <p class="text-sm text-gray-500 mt-1">Tambahkan tim/seksi pertama untuk mulai mengatur
                            struktur organisasi.</p>
                        <a href="{{ route('admin.bagian.create') }}"
                            class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            + Tambah Tim
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Nama Tim</th>
                                <th class="py-3 px-5 font-medium">Kode</th>
                                <th class="py-3 px-5 font-medium">Ketua Tim</th>
                                <th class="py-3 px-5 font-medium text-center">Pegawai</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($bagian as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3.5 px-5 font-medium text-gray-900">{{ $item->nama_bagian }}</td>

                                    <td class="py-3.5 px-5">
                                        <span
                                            class="inline-flex items-center font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md">
                                            {{ $item->kode_bagian }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-5">
                                        @if ($item->kepalaBagian)
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($item->kepalaBagian->name, 0, 1)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-900 truncate">
                                                        {{ $item->kepalaBagian->name }}</p>
                                                    <p class="text-xs text-gray-500 truncate">
                                                        {{ $item->kepalaBagian->email }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Belum ditentukan
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-5 text-center text-gray-700">{{ $item->pegawai_count }}</td>

                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center justify-end gap-2 relative">
                                            <x-dropdown-action>
                                                <a href="{{ route('admin.bagian.edit', $item) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                    Edit
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.bagian.destroy', $item) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        data-confirm="Hapus tim {{ $item->nama_bagian }}? Tindakan ini tidak bisa dibatalkan."
                                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </x-dropdown-action>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($bagian->hasPages())
                    <div class="border-t border-gray-100 px-5 py-3">
                        {{ $bagian->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>