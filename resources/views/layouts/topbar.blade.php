{{-- resources/views/layouts/topbar.blade.php --}}
@php
    $user = auth()->user();
    $namaPeran = match (true) {
        $user->isAdmin() => 'Admin',
        $user->isKepalaBps() => 'Kepala BPS Kota',
        $user->role === 'kepala_bagian' => \App\Support\ModeKerja::ketuaAktif($user)
            ? 'Kepala Tim · mode ketua'
            : 'Kepala Tim · mode anggota',
        default => 'Staf',
    };
    $menuItem =
        'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100';

    // Data lonceng disusun per peran. $notifAnggota dikirim oleh view composer;
    // mode ketua dihitung sendiri di NotifLonceng.
    $notif = \App\Support\NotifLonceng::untuk($user, $notifAnggota ?? null);
@endphp

<header class="sticky top-0 z-30 border-b border-gray-200/80 bg-white/80 backdrop-blur-md">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">

        {{-- Hamburger (mobile dan tablet) --}}
        <button type="button" @click="sidebarOpen = true" aria-label="Buka menu"
            class="-ml-1 rounded-xl p-2 text-gray-600 transition hover:bg-gray-100 active:scale-95 lg:hidden">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>

        {{-- Pencarian pekerjaan. Tekan "/" untuk langsung mengetik --}}
        <form method="GET" action="{{ route('pekerjaan.index') }}" role="search"
            class="group relative min-w-0 max-w-md flex-1"
            @keydown.window="if ($event.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.cari.focus() }">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-[#1F3864]"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
            </svg>
            <input x-ref="cari" type="search" name="cari"
                value="{{ request()->routeIs('pekerjaan.index') ? request('cari') : '' }}"
                placeholder="Cari pekerjaan..."
                class="w-full rounded-xl border-gray-200 bg-white py-2 pl-10 pr-10 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-[#1F3864] focus:ring-4 focus:ring-[#1F3864]/10">
            <kbd
                class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-gray-200 bg-gray-50 px-1.5 text-[11px] font-medium text-gray-400 md:block">/</kbd>
        </form>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">

            {{-- Lonceng notifikasi (isi menyesuaikan peran, lihat App\Support\NotifLonceng) --}}
            @if ($notif)
                <x-notif-bell :notif="$notif" />
            @endif

            {{-- Menu avatar --}}
            <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu"
                    class="flex items-center gap-2 rounded-full p-0.5 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#1F3864]/15 sm:pr-2">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#1F3864] to-[#355b91] text-sm font-semibold text-white shadow ring-2 ring-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <svg :class="open ? 'rotate-180' : ''"
                        class="hidden h-4 w-4 text-gray-400 transition-transform duration-200 sm:block" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" role="menu"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="-translate-y-1 scale-95 opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-95 opacity-0"
                    class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] origin-top-right rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-black/5">

                    <div class="px-3 pb-3 pt-2">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                        <span
                            class="mt-2 inline-flex rounded-full bg-[#DCE6F1] px-2.5 py-0.5 text-[11px] font-semibold text-[#1F3864]">
                            {{ $namaPeran }}
                        </span>
                    </div>

                    <div class="my-1 border-t border-gray-100"></div>

                    <a href="{{ route('profile.edit') }}" role="menuitem" class="{{ $menuItem }}">
                        <svg class="h-5 w-5 text-gray-400 transition-colors group-hover:text-[#1F3864]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Profil Saya
                    </a>

                    <div class="my-1 border-t border-gray-100"></div>

                    <form method="POST" action="{{ route('logout') }}" x-data="{ loading: false }"
                        @submit="loading = true">
                        @csrf
                        <button type="submit" :disabled="loading" role="menuitem"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-70">
                            <x-button-spinner x-show="loading" x-cloak class="text-red-600" />
                            <svg x-show="!loading" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
