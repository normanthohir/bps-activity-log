{{-- resources/views/approval/show.blade.php (Kepala BPS dan Ketua Tim) --}}
<x-app-layout>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <a href="{{ route($rute . '.index') }}"
            class="animate-in inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-[#1F3864] mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke daftar persetujuan
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

            {{-- Kiri: isi laporan --}}
            <div class="lg:col-span-2">
                @include('laporan.partials.isi-laporan', ['tampilPegawai' => true])
            </div>

            {{-- Kanan: keputusan dan riwayat (tetap terlihat saat halaman di-scroll) --}}
            <div class="space-y-4 lg:sticky lg:top-6">
                @include('approval.partials.keputusan')
                @include('approval.partials.riwayat')
            </div>
        </div>
    </div>
</x-app-layout>