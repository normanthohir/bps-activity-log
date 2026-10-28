{{-- resources/views/layouts/navigation.blade.php --}}
@php
    $user = auth()->user();
    $namaRoute = request()->route()?->getName() ?? '';

    $kelompok = \App\Support\MenuNavigasi::kelompok($user);
    $dashboardRoute = \App\Support\MenuNavigasi::dasbor($user);
    $dashboardAktif = in_array($namaRoute, ['dashboard', 'kabag.dashboard', 'kepala-bps.dashboard'], true);
    $profilAktif = $namaRoute === 'profile.edit';

    // Item aktif = awalan route PALING spesifik yang cocok dengan halaman sekarang.
    // Contoh: di "pekerjaan.create", yang menyala "Beri Tugas Baru", bukan "Daftar Pekerjaan IPO".
    $itemAktif = null;
    $terpanjang = -1;
    foreach ($kelompok as $g) {
        foreach ($g['items'] as $it) {
            $awalan = str_ends_with($it['route'], '.index') ? substr($it['route'], 0, -6) : $it['route'];
            if (\App\Support\MenuNavigasi::cocok($namaRoute, [$awalan]) && strlen($awalan) > $terpanjang) {
                $itemAktif = $it['route'];
                $terpanjang = strlen($awalan);
            }
        }
    }

    $namaPeran = match (true) {
        $user->isAdmin() => 'Admin',
        $user->isKepalaBps() => 'Kepala BPS Kota',
        $user->role === 'kepala_bagian' => \App\Support\ModeKerja::ketuaAktif($user)
            ? 'Kepala Tim · mode ketua'
            : 'Kepala Tim · mode anggota',
        default => 'Staf',
    };

    // Ikon (Heroicons outline)
    $ikon = [
        'dashboard' =>
            'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'pekerjaan' =>
            'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
        'standar' =>
            'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'monitoring' =>
            'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'administrasi' =>
            'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'profil' =>
            'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'keluar' =>
            'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
    ];

    $kelasAktif = 'bg-white/15 text-white shadow-sm';
    $kelasBiasa = 'text-white/70 hover:text-white hover:bg-white/10';
@endphp

{{-- Latar gelap di belakang sidebar (hanya mobile) --}}
<div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

<aside @keydown.escape.window="sidebarOpen = false" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-[#1F3864] flex flex-col transition-transform duration-200 ease-out lg:translate-x-0">

    {{-- Brand --}}
    <div class="h-16 shrink-0 flex items-center justify-between px-5 border-b border-white/10">
        <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/bps.svg') }}" alt="BPS" class="w-8 h-8 brightness-0 invert">
            <span class="text-white font-semibold text-sm leading-tight">
                RAPI<br><span class="font-normal text-white/60 text-xs">BPS Kota Ambon</span>
            </span>
        </a>

        <button @click="sidebarOpen = false" aria-label="Tutup menu"
            class="lg:hidden text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Daftar menu --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        {{-- Pengalih mode kerja: hanya untuk peran yang bisa menjadi anggota atau ketua tim --}}
        @if (in_array($user->role, ['staf', 'kepala_bagian'], true))
            <div class="pb-3 mb-2 border-b border-white/10">
                @include('layouts.partials.mode-kerja')
            </div>
        @endif
        <a href="{{ route($dashboardRoute) }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $dashboardAktif ? $kelasAktif : $kelasBiasa }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['dashboard'] }}" />
            </svg>
            Dashboard
        </a>

        @foreach ($kelompok as $g)
            @php
                // Lewati item yang route-nya belum terdaftar, supaya halaman tidak error
                $items = collect($g['items'])->filter(fn($it) => \Illuminate\Support\Facades\Route::has($it['route']));
                $grupAktif =
                    $items->contains(fn($it) => $it['route'] === $itemAktif) ||
                    \App\Support\MenuNavigasi::cocok($namaRoute, $g['active']);
            @endphp

            @continue($items->isEmpty())

            <div x-data="{ buka: {{ $grupAktif ? 'true' : 'false' }} }" class="pt-3">
                <button type="button" @click="buka = !buka"
                    class="w-full flex items-center justify-between px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-white/45 hover:text-white/70 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="{{ $ikon[$g['key']] ?? $ikon['pekerjaan'] }}" />
                        </svg>
                        {{ $g['label'] }}
                    </span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="buka ? 'rotate-180' : ''" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div x-show="buka" x-cloak x-transition.opacity.duration.150ms
                    class="mt-1 ml-5 pl-3 border-l border-white/15 space-y-0.5">
                    @foreach ($items as $it)
                        <a href="{{ route($it['route']) }}"
                            class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $it['route'] === $itemAktif ? $kelasAktif : $kelasBiasa }}">
                            {{ $it['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Bagian bawah: info user, profil, keluar --}}
    <div class="shrink-0 border-t border-white/10 p-3 space-y-1">
        <div class="flex items-center gap-3 px-3 py-2">
            <span
                class="w-9 h-9 shrink-0 rounded-full bg-white/15 text-white text-sm font-semibold flex items-center justify-center">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-white/60 truncate">{{ $namaPeran }}</p>
            </div>
        </div>

        <a href="{{ route('profile.edit') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $profilAktif ? $kelasAktif : $kelasBiasa }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['profil'] }}" />
            </svg>
            Profil Saya
        </a>

        <form method="POST" action="{{ route('logout') }}" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <x-loading-overlay message="Mengeluarkan akun..." />
            <button type="submit" :disabled="loading"
                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-red-300 hover:text-red-200 hover:bg-white/10 disabled:opacity-70 disabled:cursor-not-allowed transition-colors">
                <x-button-spinner x-show="loading" x-cloak class="text-red-300" />
                <svg x-show="!loading" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['keluar'] }}" />
                </svg>
                <span x-text="loading ? 'Keluar...' : 'Keluar'"></span>
            </button>
        </form>
    </div>
</aside>
