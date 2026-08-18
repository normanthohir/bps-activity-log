{{-- resources/views/layouts/navigation.blade.php --}}
{{-- Menu navigasi berbeda tampil otomatis sesuai role user yang login --}}
<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard') }}" class="font-semibold">DAR - BPS Kota Ambon</a>

            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600">Dashboard</a>
            <a href="{{ route('laporan.index') }}" class="text-sm text-gray-600">Laporan Saya</a>

            @if (auth()->user()->isKepalaBagian())
                <a href="{{ route('kabag.tugas.index') }}" class="text-sm text-gray-600">Tugas Tim</a>
                <a href="{{ route('kabag.approval.index') }}" class="text-sm text-gray-600">
                    Persetujuan
                </a>
            @endif

            @if (auth()->user()->isKepalaBps())
                <a href="{{ route('kepala-bps.rekap') }}" class="text-sm text-gray-600">Rekap Kantor</a>
                <a href="{{ route('kepala-bps.approval.index') }}" class="text-sm text-gray-600">
                    Persetujuan (Semua Bagian)
                </a>
            @endif

            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600">Kelola User</a>
                <a href="{{ route('admin.bagian.index') }}" class="text-sm text-gray-600">Kelola Bagian</a>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-600">Keluar</button>
        </form>
    </div>
</nav>
