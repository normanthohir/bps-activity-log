{{-- resources/views/dashboard/kepala-bagian.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-5 mb-6 flex justify-between items-center">
            <div>
                <p class="font-medium text-lg">{{ auth()->user()->name }} — Kepala Seksi</p>
                <p class="text-sm text-gray-500">
                    {{ $bagian->nama_bagian }} · {{ $staf->count() }} staf
                </p>
            </div>
            <a href="{{ route('kabag.tugas.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Beri tugas
            </a>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6 inline-block">
            <p class="text-sm text-gray-500">Menunggu persetujuan</p>
            <p class="text-2xl font-medium">{{ $menungguApproval }}</p>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <div class="flex justify-between items-center mb-3">
                <p class="font-medium">Laporan menunggu persetujuan</p>
                <a href="{{ route('kabag.approval.index') }}" class="text-sm text-gray-500">Lihat semua</a>
            </div>
            <p class="text-sm text-gray-400">Buka menu "Persetujuan" untuk memproses laporan staf.</p>
        </div>

    </div>
</x-app-layout>
