{{-- resources/views/admin/bagian/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Kelola Bagian/Seksi</h1>
                <p class="text-sm text-gray-500 mt-0.5">Struktur unit kerja dan penanggung jawab tiap bagian.</p>
            </div>
            <a href="{{ route('admin.bagian.create') }}"
               class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Bagian
            </a>
        </div>

        {{-- Summary stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500">Total Bagian</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $bagian->count() }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500">Total Pegawai</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $bagian->sum('pegawai_count') }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500">Belum Ada Kepala Bagian</p>
                <p class="text-2xl font-semibold {{ $bagian->whereNull('kepala_bagian_id')->count() > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                    {{ $bagian->whereNull('kepala_bagian_id')->count() }}
                </p>
            </div>
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($bagian->isEmpty())
                {{-- Empty state --}}
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Belum ada bagian</p>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan bagian/seksi pertama untuk mulai mengatur struktur organisasi.</p>
                    <a href="{{ route('admin.bagian.create') }}"
                       class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        + Tambah Bagian
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Nama Bagian</th>
                            <th class="py-3 px-5 font-medium">Kode</th>
                            <th class="py-3 px-5 font-medium">Kepala Bagian</th>
                            <th class="py-3 px-5 font-medium text-center">Pegawai</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($bagian as $item)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5 font-medium text-gray-900">{{ $item->nama_bagian }}</td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex items-center font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md">
                                        {{ $item->kode_bagian }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5">
                                    @if ($item->kepalaBagian)
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($item->kepalaBagian->name, 0, 1)) }}
                                            </span>
                                            <span class="text-gray-700">{{ $item->kepalaBagian->name }}</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-600 text-xs bg-amber-50 px-2 py-1 rounded-md">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            Belum ditentukan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-center text-gray-700">{{ $item->pegawai_count }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.bagian.edit', $item) }}"
                                           class="text-gray-600 hover:text-[#1F3864] text-sm font-medium transition-colors">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.bagian.destroy', $item) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" data-confirm="Hapus bagian {{ $item->nama_bagian }}? Tindakan ini tidak bisa dibatalkan."
                                                    class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
        </div>

    </div>
</x-app-layout>