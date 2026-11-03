{{-- Parameter: $terbaik, $pembinaan (daftar pegawai), $ketPembinaan (fungsi: pegawai -> keterangan), $ambang (default 70) --}}
@php use App\Support\TampilanRekap as T; @endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
            </span>
            <h2 class="font-semibold text-gray-900">Kinerja terbaik</h2>
        </div>
        <ul class="space-y-3">
            @forelse ($terbaik as $p)
                <li class="flex items-center gap-3">
                    <span class="w-6 text-center text-sm font-semibold text-gray-400">{{ $p['peringkat'] }}</span>
                    <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($p['nama'], 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $p['nama'] }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $p['bagian'] }}</p>
                    </div>
                    <span class="text-sm font-semibold {{ T::gaya($p['tingkat'])['teks'] }}">{{ $p['skor'] }}</span>
                </li>
            @empty
                <li class="text-sm text-gray-400 py-4 text-center">Belum ada data pada periode ini.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </span>
            <h2 class="font-semibold text-gray-900">Perlu pembinaan</h2>
            <span class="text-xs text-gray-400">skor di bawah {{ $ambang ?? 70 }}</span>
        </div>
        <ul class="space-y-3">
            @forelse ($pembinaan as $p)
                <li class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($p['nama'], 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $p['nama'] }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $ketPembinaan($p) }}</p>
                    </div>
                    <span class="text-sm font-semibold {{ T::gaya($p['tingkat'])['teks'] }}">{{ $p['skor'] }}</span>
                </li>
            @empty
                <li class="flex items-center justify-center gap-2 text-sm text-green-700 py-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tidak ada pegawai di bawah skor {{ $ambang ?? 70 }}.
                </li>
            @endforelse
        </ul>
    </div>
</div>