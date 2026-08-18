{{-- resources/views/dashboard/staf.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-5 mb-6 flex justify-between items-center">
            <div>
                <p class="font-medium text-lg">Halo, {{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->bagian->nama_bagian }}</p>
            </div>
            <a href="{{ route('laporan.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Laporan baru
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Tugas aktif</p>
                <p class="text-2xl font-medium">{{ $tugasAktif }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Menunggu approval</p>
                <p class="text-2xl font-medium">{{ $menungguApproval }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <p class="font-medium mb-3">Riwayat laporan terbaru</p>
            <table class="w-full text-sm">
                @forelse ($laporanTerbaru as $laporan)
                    <tr class="border-t">
                        <td class="py-2 text-gray-500">{{ $laporan->tanggal->format('d M') }}</td>
                        <td class="py-2">{{ Str::limit($laporan->uraian, 40) }}</td>
                        <td class="py-2 text-right">
                            <x-status-badge :status="$laporan->status" />
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-gray-400" colspan="3">Belum ada laporan.</td></tr>
                @endforelse
            </table>
        </div>

    </div>
</x-app-layout>
