{{-- Sumber menu bersama: App\Support\MenuNavigasi, digunakan oleh desktop & mobile. --}}
@php
    $akun = auth()->user();
    $menu = \App\Support\MenuNavigasi::kelompok($akun);
    $routeAktif = request()->route()?->getName() ?? '';
    $routeDasbor = \App\Support\MenuNavigasi::dasbor($akun);
    $dasborAktif = \App\Support\MenuNavigasi::cocok($routeAktif, ['dashboard', 'kabag.dashboard', 'kepala-bps.dashboard']);
    $kelompokAktif = collect($menu)->first(fn ($item) => \App\Support\MenuNavigasi::cocok($routeAktif, $item['active']));
    $mobileAwal = $kelompokAktif['key'] ?? null;
@endphp
<nav class="bg-[#1F3864] shadow-lg sticky top-0 z-50"
     x-data="{ menuTerbuka: null, mobileTerbuka: false, subMobile: @js($mobileAwal) }"
     @keydown.escape.window="menuTerbuka = null; mobileTerbuka = false; subMobile = null">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 h-16">
            <a href="{{ route($routeDasbor) }}" class="flex items-center gap-2.5 shrink-0" aria-label="Beranda RAPI">
                <img src="{{ asset('images/bps.svg') }}" alt="Logo BPS" class="w-8 h-8 brightness-0 invert">
                <span class="text-white font-semibold text-sm tracking-wide hidden sm:block xl:max-w-[180px] 2xl:max-w-none">
                    RAPI - BPS Kota Ambon
                </span>
            </a>

            {{-- Navigasi desktop: menu dikelompokkan menurut fungsi, bukan seluruh tautan berjejer. --}}
            <div class="hidden xl:flex items-center gap-1" aria-label="Navigasi utama">
                <a href="{{ route($routeDasbor) }}" data-nav-link="dashboard"
                   @class(['px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                     'bg-white/15 text-white' => $dasborAktif,
                     'text-white/80 hover:text-white hover:bg-white/10' => !$dasborAktif])
                   @if($dasborAktif) aria-current="page" @endif>Dashboard</a>

                @foreach ($menu as $kelompok)
                    @php
                        $aktif = \App\Support\MenuNavigasi::cocok($routeAktif, $kelompok['active']);
                        $key = $kelompok['key'];
                    @endphp
                    <div class="relative" style="position: relative;" data-nav-group="{{ $key }}"
                         @click.outside="if (menuTerbuka === @js($key)) menuTerbuka = null">
                        <button type="button" @click="menuTerbuka = menuTerbuka === @js($key) ? null : @js($key)"
                                :aria-expanded="menuTerbuka === @js($key) ? 'true' : 'false'"
                                aria-haspopup="true" aria-controls="menu-desktop-{{ $key }}"
                                @class(['flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                  'bg-white/15 text-white' => $aktif,
                                  'text-white/80 hover:text-white hover:bg-white/10' => !$aktif])>
                            <span>{{ $kelompok['label'] }}</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 :class="{ 'rotate-180': menuTerbuka === @js($key) }" class="transition-transform" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <div id="menu-desktop-{{ $key }}" x-show="menuTerbuka === @js($key)" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute min-w-[240px] rounded-xl bg-white border border-gray-200 shadow-xl p-1.5 z-50"
                             style="position: absolute; top: calc(100% + 8px); left: 0; min-width: 240px; z-index: 80;">
                            <div class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                {{ $kelompok['label'] }}
                            </div>
                            @foreach ($kelompok['items'] as $tautan)
                                @php $tautanAktif = \App\Support\MenuNavigasi::cocok($routeAktif, [$tautan['route']]); @endphp
                                <a href="{{ route($tautan['route']) }}" data-nav-link="{{ $tautan['route'] }}"
                                   @class(['block rounded-lg px-3 py-2.5 text-sm transition-colors',
                                     'bg-blue-50 text-[#1F3864] font-semibold' => $tautanAktif,
                                     'text-slate-700 hover:bg-slate-100' => !$tautanAktif])
                                   @if($tautanAktif) aria-current="page" @endif>
                                    {{ $tautan['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Dropdown akun berjangkar pada tombol akun, bukan pada halaman. --}}
            <div class="hidden xl:flex items-center relative shrink-0" style="position: relative;"
                 x-data="{ akunTerbuka: false }" @click.outside="akunTerbuka = false" @keydown.escape.window="akunTerbuka = false">
                <button type="button" @click="akunTerbuka = !akunTerbuka; menuTerbuka = null"
                        :aria-expanded="akunTerbuka ? 'true' : 'false'" aria-controls="menu-akun-desktop"
                        class="flex items-center gap-2 text-white/80 hover:text-white text-sm font-medium px-2 py-2 rounded-lg hover:bg-white/10 transition-colors">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-white/15 text-white text-xs font-semibold flex items-center justify-center">
                        {{ strtoupper(substr($akun->name, 0, 1)) }}
                    </span>
                    <span class="max-w-[165px] truncate" style="max-width: 165px;">{{ $akun->name }}</span>
                    <span class="hidden 2xl:inline text-[10px] px-2 py-0.5 rounded-full bg-white/15">{{ \App\Support\ModeKerja::label($akun) }}</span>
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
                <div id="menu-akun-desktop" x-show="akunTerbuka" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute w-72 bg-white rounded-xl shadow-xl border border-gray-200 py-1 z-50"
                     style="position: absolute; top: calc(100% + 8px); right: 0; max-width: calc(100vw - 2rem); z-index: 80;">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900 break-words">{{ $akun->name }}</p>
                        <p class="text-xs text-gray-500 break-all">{{ $akun->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ \App\Support\ModeKerja::label($akun) }}</p>
                    </div>
                    @if($akun->isStaf())
                        @include('layouts.partials.mode-kerja')
                    @endif
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Profil Saya</a>
                    <hr class="my-1 border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">Keluar</button>
                    </form>
                </div>
            </div>

            {{-- Tombol menu layar kecil/tablet. --}}
            <button type="button" class="xl:hidden text-white p-2 rounded-lg hover:bg-white/10"
                    @click="mobileTerbuka = !mobileTerbuka; if (!mobileTerbuka) subMobile = null"
                    :aria-expanded="mobileTerbuka ? 'true' : 'false'" aria-controls="mobile-nav"
                    aria-label="Buka atau tutup menu navigasi">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile memakai konfigurasi menu dan hak akses yang sama dengan desktop. --}}
    <div id="mobile-nav" x-show="mobileTerbuka" x-cloak class="xl:hidden border-t border-white/10"
         style="max-height: calc(100vh - 64px); overflow-y: auto;">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route($routeDasbor) }}" data-nav-link="dashboard"
               @class(['block rounded-lg px-3 py-2.5 text-sm font-medium',
                 'bg-white/15 text-white' => $dasborAktif,
                 'text-white/80 hover:text-white hover:bg-white/10' => !$dasborAktif])>Dashboard</a>

            @foreach ($menu as $kelompok)
                @php
                    $aktif = \App\Support\MenuNavigasi::cocok($routeAktif, $kelompok['active']);
                    $key = $kelompok['key'];
                @endphp
                <div data-nav-group="{{ $key }}">
                    <button type="button" class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-medium"
                            @class(['bg-white/15 text-white' => $aktif, 'text-white/80 hover:bg-white/10 hover:text-white' => !$aktif])
                            @click="subMobile = subMobile === @js($key) ? null : @js($key)"
                            :aria-expanded="subMobile === @js($key) ? 'true' : 'false'"
                            aria-controls="mobile-group-{{ $key }}">
                        <span>{{ $kelompok['label'] }}</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             :class="{ 'rotate-180': subMobile === @js($key) }" class="transition-transform" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div id="mobile-group-{{ $key }}" x-show="subMobile === @js($key)" x-cloak class="pl-3 pb-2 space-y-0.5">
                        @foreach ($kelompok['items'] as $tautan)
                            @php $tautanAktif = \App\Support\MenuNavigasi::cocok($routeAktif, [$tautan['route']]); @endphp
                            <a href="{{ route($tautan['route']) }}" data-nav-link="{{ $tautan['route'] }}"
                               @class(['block rounded-lg px-3 py-2.5 text-sm',
                                 'bg-white/15 text-white font-semibold' => $tautanAktif,
                                 'text-white/75 hover:text-white hover:bg-white/10' => !$tautanAktif])>
                                {{ $tautan['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-3 pt-3 border-t border-white/15 text-white">
                <p class="text-sm font-semibold px-3 break-words">{{ $akun->name }}</p>
                <p class="text-xs text-white/60 px-3 mt-0.5 break-all">{{ $akun->email }}</p>
                <p class="text-xs text-white/70 px-3 mt-1">{{ \App\Support\ModeKerja::label($akun) }}</p>
                @if($akun->isStaf())
                    <div class="px-2 py-2 rounded-lg bg-white/10 mt-2">
                        @include('layouts.partials.mode-kerja', ['modeMobile' => true])
                    </div>
                @endif
                <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2.5 text-sm text-white/80 hover:bg-white/10 mt-2">Profil Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left rounded-lg px-3 py-2.5 text-sm text-red-200 hover:bg-white/10">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</nav>
