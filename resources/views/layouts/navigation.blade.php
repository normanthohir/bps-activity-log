    {{-- resources/views/layouts/navigation.blade.php --}}
    @php
        $user = auth()->user();
        $namaRoute = request()->route()?->getName() ?? '';

        $kelompok = \App\Support\MenuNavigasi::kelompok($user);
        // $dashboardRoute = \App\Support\MenuNavigasi::dasbor($user);
        // $dashboardAktif = in_array($namaRoute, ['dashboard', 'kabag.dashboard', 'kepala-bps.dashboard'], true);
        $dashboardRoute = 'dashboard';
        $dashboardAktif = $namaRoute === 'dashboard';
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

            'laporan' =>
                'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            'persetujuan' =>
                'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75',
        ];
        $kelasAktif = 'bg-white/15 text-white ring-1 ring-white/10 shadow-sm';
        $kelasBiasa = 'text-white/70 hover:bg-white/10 hover:text-white';
        $baris = 'group relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
    duration-200 motion-reduce:transition-none';
        $ikonKelas = 'h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-110
    motion-reduce:transition-none';
        $penanda = 'absolute -left-3 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-sky-300';
        $tooltip = 'pointer-events-none absolute left-full top-1/2 z-[60] ml-3 hidden -translate-x-1 -translate-y-1/2
    whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition
    duration-150 group-hover:translate-x-0 group-hover:opacity-100 lg:block';
    @endphp

    {{-- Latar gelap di belakang sidebar (hanya mobile) --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

    <aside @keydown.escape.window="sidebarOpen = false"
        :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', collapsed ? 'lg:!w-[76px]' : '']"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#1F3864] shadow-2xl transition-[transform,width] duration-300 ease-out motion-reduce:transition-none lg:w-64 lg:translate-x-0 lg:shadow-none">

        {{-- Tombol ciutkan / lebarkan (desktop) --}}
        <button type="button" @click="toggle()" aria-label="Ciutkan atau lebarkan menu"
            :aria-expanded="(!collapsed).toString()"
            class="absolute -right-3 top-[22px] z-[61] hidden h-6 w-6 items-center justify-center rounded-full border border-gray-200 bg-white text-[#1F3864] shadow-md transition hover:scale-110 active:scale-95 motion-reduce:transition-none lg:flex">
            <svg :class="collapsed ? 'rotate-180' : ''" class="h-3.5 w-3.5 transition-transform duration-300"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        {{-- Brand --}}
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-white/10 px-4"
            :class="collapsed ? 'lg:justify-center lg:px-0' : ''">
            <a href="{{ route($dashboardRoute) }}" class="flex min-w-0 items-center gap-2.5">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                    <img src="{{ asset('images/bps.svg') }}" alt="BPS" class="h-6 w-6 brightness-0 invert">
                </span>
                <span class="min-w-0 text-sm font-semibold leading-tight text-white"
                    :class="collapsed ? 'lg:hidden' : ''">
                    RAPI<br><span class="text-xs font-normal text-white/60">BPS Kota Ambon</span>
                </span>
            </a>

            <button @click="sidebarOpen = false" aria-label="Tutup menu"
                class="rounded-lg p-1.5 text-white/70 transition hover:bg-white/10 hover:text-white lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Daftar menu. Saat dicitutkan, tidak boleh memotong tooltip dan flyout --}}
        <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 py-4" :class="collapsed ? 'lg:overflow-visible' : ''">

            {{-- Pengalih mode kerja (disembunyikan saat sidebar diciutkan) --}}
            @if (in_array($user->role, ['staf', 'kepala_bagian'], true))
                <div class="mb-2 border-b border-white/10 pb-3" :class="collapsed ? 'lg:hidden' : ''">
                    @include('layouts.partials.mode-kerja')
                </div>
            @endif

            {{-- Dashboard --}}
            <a href="{{ route($dashboardRoute) }}"
                class="{{ $baris }} {{ $dashboardAktif ? $kelasAktif : $kelasBiasa }}">
                @if ($dashboardAktif)
                    <span class="{{ $penanda }}"></span>
                @endif
                <svg class="{{ $ikonKelas }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['dashboard'] }}" />
                </svg>
                <span class="truncate" :class="collapsed ? 'lg:hidden' : ''">Dashboard</span>
                <span x-show="collapsed" x-cloak class="{{ $tooltip }}">Dashboard</span>
            </a>

            @foreach ($kelompok as $g)
                @php
                    // Lewati item yang route-nya belum terdaftar, supaya halaman tidak error
                    $items = collect($g['items'])->filter(
                        fn($it) => \Illuminate\Support\Facades\Route::has($it['route']),
                    );
                    $grupAktif =
                        $items->contains(fn($it) => $it['route'] === $itemAktif) ||
                        \App\Support\MenuNavigasi::cocok($namaRoute, $g['active']);
                @endphp

                @continue($items->isEmpty())

                {{-- Hanya satu item: tautan langsung --}}
                @if ($items->count() === 1)
                    @php
                        $tunggal = $items->first();
                        $aktifTunggal = $tunggal['route'] === $itemAktif;
                        $ikonTunggal = $ikon[$tunggal['ikon'] ?? $g['key']] ?? $ikon['pekerjaan'];
                    @endphp
                    <a href="{{ route($tunggal['route']) }}"
                        class="{{ $baris }} {{ $aktifTunggal ? $kelasAktif : $kelasBiasa }}">
                        @if ($aktifTunggal)
                            <span class="{{ $penanda }}"></span>
                        @endif
                        <svg class="{{ $ikonKelas }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonTunggal }}" />
                        </svg>
                        <span class="truncate" :class="collapsed ? 'lg:hidden' : ''">{{ $tunggal['label'] }}</span>
                        <span x-show="collapsed" x-cloak class="{{ $tooltip }}">{{ $tunggal['label'] }}</span>
                    </a>
                    @continue
                @endif

                {{-- Grup dengan banyak item --}}
                <div x-data="{ buka: {{ $grupAktif ? 'true' : 'false' }} }" class="group/g relative">
                    <button type="button" @click="buka = !buka" :aria-expanded="buka.toString()"
                        :class="collapsed ? '{{ $grupAktif ? 'lg:bg-white/15 lg:ring-1 lg:ring-white/10' : '' }}' : ''"
                        class="{{ $baris }} {{ $grupAktif ? 'text-white' : $kelasBiasa }}">
                        <svg class="{{ $ikonKelas }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="{{ $ikon[$g['key']] ?? $ikon['pekerjaan'] }}" />
                        </svg>
                        <span class="flex-1 truncate text-left"
                            :class="collapsed ? 'lg:hidden' : ''">{{ $g['label'] }}</span>
                        <svg class="h-4 w-4 shrink-0 text-white/50 transition-transform duration-300 motion-reduce:transition-none"
                            :class="[buka ? 'rotate-180' : '', collapsed ? 'lg:hidden' : '']" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Submenu (sidebar lebar): garis pohon seperti di contoh, dibuka dengan animasi tinggi --}}
                    <div :class="[buka ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]', collapsed ? 'lg:hidden' : '']"
                        class="grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none">
                        <div class="overflow-hidden">
                            <ul class="ml-[22px] mt-1 space-y-0.5 border-l border-white/15 pl-3">
                                @foreach ($items as $it)
                                    @php $aktifAnak = $it['route'] === $itemAktif; @endphp
                                    <li>
                                        <a href="{{ route($it['route']) }}"
                                            class="block rounded-lg px-3 py-2 text-[13px] font-medium transition-all duration-200 hover:translate-x-0.5 motion-reduce:transition-none {{ $aktifAnak ? $kelasAktif : $kelasBiasa }}">
                                            {{ $it['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Flyout (sidebar diciutkan): muncul saat disorot atau difokuskan --}}
                    <div x-show="collapsed" x-cloak
                        class="invisible absolute left-full top-0 z-[60] hidden -translate-x-1 pl-3 opacity-0 transition duration-150 group-hover/g:visible group-hover/g:translate-x-0 group-hover/g:opacity-100 group-focus-within/g:visible group-focus-within/g:translate-x-0 group-focus-within/g:opacity-100 lg:block">
                        <div class="w-56 rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-black/5">
                            <p
                                class="px-3 pb-1.5 pt-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                {{ $g['label'] }}
                            </p>
                            @foreach ($items as $it)
                                <a href="{{ route($it['route']) }}"
                                    class="block rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $it['route'] === $itemAktif ? 'bg-[#1F3864]/10 text-[#1F3864]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                    {{ $it['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>

        {{-- Bawah: kartu pengguna (Profil dan Keluar ada di menu avatar pada topbar) --}}
        <div class="shrink-0 border-t border-white/10 p-3">
            <a href="{{ route('profile.edit') }}"
                class="{{ $baris }} !gap-3 {{ $profilAktif ? $kelasAktif : $kelasBiasa }}">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-white/25 to-white/10 text-sm font-semibold text-white ring-1 ring-white/20">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1" :class="collapsed ? 'lg:hidden' : ''">
                    <span class="block truncate text-sm font-semibold text-white">{{ $user->name }}</span>
                    <span class="block truncate text-xs font-normal text-white/60">{{ $namaPeran }}</span>
                </span>
                <span x-show="collapsed" x-cloak class="{{ $tooltip }}">{{ $user->name }}</span>
            </a>
        </div>
    </aside>
