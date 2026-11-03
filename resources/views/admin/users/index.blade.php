{{-- resources/views/admin/users/index.blade.php --}}
@php
    $roleGaya = [
        'admin' => 'bg-red-50 text-red-700 ring-red-600/20',
        'kepala_bps' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'kepala_bagian' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'staf' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
    // Urutan ini juga dipakai sebagai isi filter role
    $roleNama = ['staf' => 'Staf', 'kepala_bagian' => 'Kepala Tim', 'kepala_bps' => 'Kepala BPS', 'admin' => 'Admin'];

    $adaFilter = collect(['search', 'role', 'bagian', 'status'])->contains(fn($k) => request()->filled($k));

    $ikon = [
        'akun' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'staf' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'ketua' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        'surat' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        'tim' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    ];

    $kartu = [
        ['label' => 'Total akun', 'nilai' => $ringkasan['total'] ?? 0, 'warna' => 'blue', 'ikon' => $ikon['akun'], 'url' => route('admin.users.index')],
        ['label' => 'Staf', 'nilai' => $ringkasan['staf'] ?? 0, 'warna' => 'green', 'ikon' => $ikon['staf'], 'url' => route('admin.users.index', ['role' => 'staf'])],
        ['label' => 'Kepala tim', 'nilai' => $ringkasan['kepala_tim'] ?? 0, 'warna' => 'indigo', 'ikon' => $ikon['ketua'], 'url' => route('admin.users.index', ['role' => 'kepala_bagian'])],
        ['label' => 'Belum verifikasi', 'nilai' => $ringkasan['belum_verifikasi'] ?? 0, 'warna' => 'amber', 'ikon' => $ikon['surat'], 'soroti' => true, 'url' => route('admin.users.index', ['status' => 'belum'])],
    ];

    $filter = [
        ['name' => 'role', 'semua' => 'Semua role', 'opsi' => $roleNama],
        ['name' => 'bagian', 'semua' => 'Semua tim', 'opsi' => $daftarBagian->pluck('nama_bagian', 'id')->all()],
        ['name' => 'status', 'semua' => 'Semua status', 'opsi' => ['terverifikasi' => 'Terverifikasi', 'belum' => 'Belum verifikasi']],
    ];
@endphp

<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        @include('admin.partials.header-halaman', [
            'judul' => 'Kelola Akun Pegawai',
            'deskripsi' => 'Semua pengguna sistem beserta peran dan tim.',
            'tombol' => 'Tambah Akun',
            'href' => route('admin.users.create'),
        ])

        @include('admin.partials.kartu-ringkasan', ['kartu' => $kartu])

        @include('admin.partials.bar-filter', [
            'aksi' => route('admin.users.index'),
            'cari' => 'Cari nama, NIP, atau email...',
            'filter' => $filter,
            'adaFilter' => $adaFilter,
        ])

        <div class="animate-in overflow-hidden rounded-2xl border border-gray-200 bg-white" style="animation-delay: .28s">
            @if ($users->isEmpty())
                <div class="px-4 py-16 text-center">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['akun'] }}" /></svg>
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-900">{{ $adaFilter ? 'Tidak ada akun yang cocok' : 'Belum ada akun pegawai' }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $adaFilter ? 'Coba ubah kata kunci atau filter yang dipakai.' : 'Tambahkan akun pertama untuk mulai mengelola pengguna.' }}
                    </p>
                    @if ($adaFilter)
                        <a href="{{ route('admin.users.index') }}" class="mt-4 inline-block text-sm font-medium text-[#1F3864] hover:underline">Reset filter</a>
                    @endif
                </div>
            @else
                {{-- Jumlah hasil + judul kolom (kolom hanya di layar md ke atas) --}}
                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/70 px-4 py-2 text-xs text-gray-500">
                    <span><span class="font-semibold tabular-nums text-gray-700">{{ $users->total() }}</span> akun</span>
                    <span class="hidden items-center gap-4 text-[11px] font-semibold uppercase tracking-wide text-gray-400 md:flex">
                        <span class="w-28">Role</span><span class="w-44">Tim</span><span class="w-36">Status</span><span class="w-10"></span>
                    </span>
                </div>

                <ul class="divide-y divide-gray-100">
                    @foreach ($users as $i => $user)
                        <li class="animate-in group flex flex-wrap items-center gap-x-4 gap-y-2.5 px-4 py-3.5 transition-colors hover:bg-blue-50/40 md:flex-nowrap motion-reduce:transition-none"
                            style="animation-delay: {{ 0.32 + min($i, 10) * 0.035 }}s">

                            {{-- Pegawai --}}
                            <div class="order-1 flex min-w-0 flex-1 items-center gap-3 md:order-none">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#DCE6F1] text-sm font-semibold text-[#1F3864] transition-transform duration-200 group-hover:scale-105 motion-reduce:transition-none">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="ml-1 rounded-full bg-[#1F3864]/10 px-1.5 py-0.5 align-middle text-[10px] font-medium text-[#1F3864]">Anda</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                                    <p class="truncate font-mono text-[11px] text-gray-400">{{ $user->nip ?: '-' }}</p>
                                </div>
                            </div>

                            {{-- Badge: satu baris di mobile, kolom di layar md ke atas --}}
                            <div class="order-3 flex w-full flex-wrap items-center gap-2 md:order-none md:contents">
                                <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset md:w-28 md:justify-center {{ $roleGaya[$user->role] ?? $roleGaya['staf'] }}">
                                    {{ $roleNama[$user->role] ?? $user->role }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 truncate text-xs text-gray-600 md:w-44 md:text-sm">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['tim'] }}" /></svg>
                                    <span class="truncate">{{ $user->bagian?->nama_bagian ?? '-' }}</span>
                                </span>

                                @if ($user->hasVerifiedEmail())
                                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 md:w-36">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 md:w-36">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75 motion-reduce:animate-none"></span>
                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        </span>
                                        Belum verifikasi
                                    </span>
                                @endif
                            </div>

                            {{-- Aksi --}}
                            <div class="order-2 flex shrink-0 justify-end md:order-none md:w-10">
                                <x-dropdown-action>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100">Edit</a>

                                    @if (!$user->hasVerifiedEmail())
                                        <form method="POST" action="{{ route('admin.users.resend-verification', $user) }}">
                                            @csrf
                                            <button class="w-full px-4 py-2 text-left text-sm text-amber-700 transition-colors hover:bg-amber-50">
                                                Kirim ulang verifikasi
                                            </button>
                                        </form>
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                            @csrf
                                            <button type="submit"
                                                data-confirm="Password {{ $user->name }} akan dikembalikan ke default (password)."
                                                data-confirm-ok="Ya, reset"
                                                class="w-full px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-100">
                                                Reset password
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                data-confirm="Hapus akun {{ $user->name }}? Tindakan ini tidak bisa dibatalkan."
                                                class="w-full px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </x-dropdown-action>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($users->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3">{{ $users->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>