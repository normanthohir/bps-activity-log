@php $daftar = $pengingatLaporan ?? collect(); @endphp

@if ($daftar->isNotEmpty())
    <div class="animate-in mb-5 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4">
        <div class="flex items-center gap-2.5 mb-3">
            <span class="relative flex h-8 w-8 items-center justify-center rounded-lg bg-green-600 text-white">
                <span class="absolute inline-flex h-full w-full rounded-lg bg-green-400 opacity-40 animate-ping motion-reduce:animate-none"></span>
                <svg class="relative h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-green-900">
                    {{ $daftar->count() }} pekerjaan selesai, belum dilaporkan
                </p>
                <p class="text-xs text-green-700">Buat laporan agar kegiatan Anda tercatat.</p>
            </div>
        </div>

        <ul class="space-y-2">
            @foreach ($daftar->take(3) as $t)
                <li class="flex items-center justify-between gap-3 rounded-xl bg-white/80 px-3 py-2.5 transition hover:bg-white">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">{{ $t->judul }}</p>
                        <p class="text-xs text-gray-500">Selesai {{ $t->updated_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('laporan.create', ['tugas' => $t->id]) }}"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-green-700 active:scale-95 motion-reduce:transition-none">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat laporan
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($daftar->count() > 3)
            <a href="{{ route('pekerjaan.index', ['status' => 'selesai']) }}"
                class="mt-3 inline-block text-xs font-semibold text-green-800 hover:underline">
                +{{ $daftar->count() - 3 }} pekerjaan lainnya →
            </a>
        @endif
    </div>
@endif