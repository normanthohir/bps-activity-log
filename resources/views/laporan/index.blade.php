{{-- resources/views/laporan/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Daftar Laporan Harian</h1>
                <p class="text-sm text-gray-500 mt-0.5">Riwayat laporan aktivitas yang telah Anda buat.</p>
            </div>
            @if (auth()->user()->role === 'staf')
                <a href="{{ route('laporan.create') }}"
                    class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    Laporan Baru
                </a>
            @endif
            @if (auth()->user()->role === 'kepala_bagian')
                <a href="{{ route('kabag.laporan.create') }}"
                    class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    Laporan Baru
                </a>
            @endif

        </div>

        {{-- Filter by status --}}
        <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
            <select name="status" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
            </select>
            @if (request('status'))
                <a href="{{ route('laporan.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
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
                                    @if (auth()->user()->role === 'staf')
                                        <a href="{{ route('laporan.edit', $item) }}" class="underline text-sm">
                                            {{ $item->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                                        </a>
                                    @else
                                        <a href="{{ route('kabag.laporan.edit', $item) }}" class="underline text-sm">
                                            {{ $item->status === 'dikembalikan' ? 'Revisi' : 'Edit' }}
                                        </a>
                                    @endif
                                @else
                                    @if (auth()->user()->role === 'staf')
                                        <a href="{{ route('laporan.show', $item) }}" class="underline text-sm">
                                            Lihat
                                        </a>
                                    @else
                                        <a href="{{ route('kabag.laporan.show', $item) }}" class="underline text-sm">
                                            Lihat
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-400">Belum ada laporan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
</x-app-layout>
