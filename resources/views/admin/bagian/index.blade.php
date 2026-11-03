{{-- resources/views/admin/bagian/index.blade.php --}}
@php
    $adaFilter = collect(['search', 'status'])->contains(fn($k) => request()->filled($k));

    $ikon = [
        'tim' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
        'pegawai' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'peringatan' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    ];

    $kartu = [
        ['label' => 'Total tim', 'nilai' => $ringkasan['total'] ?? 0, 'warna' => 'indigo', 'ikon' => $ikon['tim'], 'url' => route('admin.bagian.index')],
        ['label' => 'Total pegawai', 'nilai' => $ringkasan['pegawai'] ?? 0, 'warna' => 'blue', 'ikon' => $ikon['pegawai']],
        ['label' => 'Belum ada ketua', 'nilai' => $ringkasan['belum_kepala'] ?? 0, 'warna' => 'amber', 'ikon' => $ikon['peringatan'], 'soroti' => true, 'url' => route('admin.bagian.index', ['status' => 'belum'])],
    ];

    $filter = [
        ['name' => 'status', 'semua' => 'Semua tim', 'opsi' => ['ada' => 'Sudah ada ketua', 'belum' => 'Belum ada ketua']],
    ];
@endphp

<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        @include('admin.partials.header-halaman', [
            'judul' => 'Kelola Tim/Seksi',
            'deskripsi' => 'Struktur unit kerja dan penanggung jawab tiap tim.',
            'tombol' => 'Tambah Tim',
            'href' => route('admin.bagian.create'),
        ])

        @include('admin.partials.kartu-ringkasan', ['kartu' => $kartu])

        @include('admin.partials.bar-filter', [
            'aksi' => route('admin.bagian.index'),
            'cari' => 'Cari nama atau kode tim...',
            'filter' => $filter,
            'adaFilter' => $adaFilter,
        ])

        <div class="animate-in overflow-hidden rounded-2xl border border-gray-200 bg-white" style="animation-delay: .28s">
            @if ($bagian->isEmpty())
                <div class="px-4 py-16 text-center">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['tim'] }}" /></svg>
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900">{{ $adaFilter ? 'Tidak ada tim yang cocok' : 'Belum ada tim' }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $adaFilter ? 'Coba ubah kata kunci atau filter yang dipakai.' : 'Tambahkan tim/seksi pertama untuk mulai mengatur struktur organisasi.' }}
                    </p>
                    @if ($adaFilter)
                        <a href="{{ route('admin.bagian.index') }}" class="mt-4 inline-block text-sm font-medium text-[#1F3864] hover:underline">Reset filter</a>
                    @else
                        <a href="{{ route('admin.bagian.create') }}" class="mt-4 inline-block text-sm font-medium text-[#1F3864] hover:underline">+ Tambah Tim</a>
                    @endif
                </div>
            @else
                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/70 px-4 py-2 text-xs text-gray-500">
                    <span><span class="font-semibold tabular-nums text-gray-700">{{ $bagian->total() }}</span> tim</span>
                    <span class="hidden items-center gap-4 text-[11px] font-semibold uppercase tracking-wide text-gray-400 md:flex">
                        <span class="w-60">Ketua tim</span><span class="w-24">Pegawai</span><span class="w-10"></span>
                    </span>
                </div>

                <ul class="divide-y divide-gray-100">
                    @foreach ($bagian as $i => $item)
                        <li class="animate-in group flex flex-wrap items-center gap-x-4 gap-y-2.5 px-4 py-3.5 transition-colors hover:bg-blue-50/40 md:flex-nowrap motion-reduce:transition-none"
                            style="animation-delay: {{ 0.32 + min($i, 10) * 0.035 }}s">

                            {{-- Tim --}}
                            <div class="order-1 flex min-w-0 flex-1 items-center gap-3 md:order-none">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#DCE6F1] text-[#1F3864] transition-transform duration-200 group-hover:scale-105 motion-reduce:transition-none">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['tim'] }}" /></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $item->nama_bagian }}</p>
                                    <span class="mt-0.5 inline-flex rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-[11px] text-gray-600">{{ $item->kode_bagian }}</span>
                                </div>
                            </div>

                            {{-- Ketua dan jumlah pegawai --}}
                            <div class="order-3 flex w-full flex-wrap items-center gap-x-4 gap-y-2 md:order-none md:contents">
                                <div class="min-w-0 md:w-60">
                                    @if ($item->kepalaBagian)
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                {{ strtoupper(substr($item->kepalaBagian->name, 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-800">{{ $item->kepalaBagian->name }}</p>
                                                <p class="truncate text-xs text-gray-500">{{ $item->kepalaBagian->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Belum ditentukan
                                        </span>
                                    @endif
                                </div>

                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium tabular-nums text-gray-700 md:w-24 md:justify-center">
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['pegawai'] }}" /></svg>
                                    {{ $item->pegawai_count }}
                                </span>
                            </div>

                            {{-- Aksi --}}
                            <div class="order-2 flex shrink-0 justify-end md:order-none md:w-10">
                                <x-dropdown-action>
                                    <a href="{{ route('admin.bagian.edit', $item) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.bagian.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            data-confirm="Hapus tim {{ $item->nama_bagian }}? Tindakan ini tidak bisa dibatalkan."
                                            class="w-full px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                </x-dropdown-action>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($bagian->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3">{{ $bagian->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>