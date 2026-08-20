{{-- resources/views/approval/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Persetujuan Laporan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Laporan dari staf yang menunggu persetujuan Anda.</p>
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($laporan->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada laporan yang menunggu</p>
                    <p class="text-sm text-gray-500 mt-1">Semua laporan staf sudah diproses.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Pegawai</th>
                            <th class="py-3 px-5 font-medium">Tanggal</th>
                            <th class="py-3 px-5 font-medium">Uraian</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($laporan as $item)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $item->user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-gray-500 whitespace-nowrap">{{ $item->tanggal->format('d M Y') }}</td>
                                <td class="py-3.5 px-5 text-gray-700">{{ Str::limit($item->uraian, 45) }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('kabag.approval.proses', $item) }}">
                                            @csrf
                                            <input type="hidden" name="aksi" value="disetujui">
                                            <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('kabag.approval.proses', $item) }}">
                                            @csrf
                                            <input type="hidden" name="aksi" value="ditolak">
                                            <button class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $laporan->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
