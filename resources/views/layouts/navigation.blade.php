{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="bg-[#1F3864] shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/bps.svg') }}" alt="BPS" class="w-8 h-8 brightness-0 invert">
                    <span class="text-white font-semibold text-sm tracking-wide hidden sm:block">RAPI - BPS Kota
                        Ambon</span>
                </a>
            </div>

            {{-- Navigation links --}}
            <div class="hidden md:flex items-center gap-1">
                @php
                    $currentRoute = request()->route()->getName();
                @endphp

                @php
                    $dashboardRoute = match (auth()->user()->role) {
                        'kepala_bagian' => 'kabag.dashboard',
                        'kepala_bps' => 'kepala-bps.dashboard',
                        default => 'dashboard', // staf & admin
                    };
                @endphp

                <a href="{{ route($dashboardRoute) }}"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ in_array($currentRoute, ['dashboard', 'kabag.dashboard', 'kepala-bps.dashboard']) ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                    Dashboard
                </a>

                @if (auth()->user()->role === 'staf')
                    <a href="{{ route('laporan.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'laporan') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Laporan
                    </a>
                @endif


                @if (auth()->user()->role === 'staf')
                    <a href="{{ route('staf.tugas.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'staf.tugas') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Tugas Aktif
                    </a>
                @endif

                @if (auth()->user()->isKepalaBagian())
                    <a href="{{ route('kabag.laporan.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kabag.laporan') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Laporan
                    </a>
                    <a href="{{ route('kabag.tugas-aktif.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kabag.tugas-aktif.index') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Tugas Aktif
                    </a>
                    <a href="{{ route('kabag.tugas.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kabag.tugas.index') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Tugas Tim
                    </a>

                    <a href="{{ route('kabag.approval.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kabag.approval') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Persetujuan
                    </a>
                @endif

                @if (auth()->user()->isKepalaBps())
                    <a href="{{ route('kepala-bps.rekap') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kepala-bps.rekap') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Rekap Kantor
                    </a>
                    <a href="{{ route('kepala-bps.tugas.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kepala-bps.tugas') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Beri Tugas
                    </a>
                    <a href="{{ route('kepala-bps.approval.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'kepala-bps.approval') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Persetujuan
                    </a>
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'admin.users') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Kelola User
                    </a>
                    <a href="{{ route('admin.bagian.index') }}"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ str_starts_with($currentRoute, 'admin.bagian') ? 'bg-white/15 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        Kelola Bagian
                    </a>
                @endif
            </div>

            {{-- User dropdown --}}
            <div class="hidden md:flex items-center" x-data="{ open: false }">
                <button @click="open = !open" @keydown.escape.window="open = false"
                    class="flex items-center gap-2 text-white/80 hover:text-white text-sm font-medium px-3 py-2 rounded-lg hover:bg-white/10 transition-colors">
                    <span
                        class="w-8 h-8 rounded-full bg-white/15 text-white text-xs font-semibold flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span>{{ auth()->user()->name }}</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" @click.away="open = false"
                    class="absolute right-4 top-14 w-56 bg-white rounded-xl shadow-xl border border-gray-200 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Profile Saya
                    </a>
                    <hr class="my-1 border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Mobile hamburger --}}
            <div class="md:hidden flex items-center">
                <button onclick="document.getElementById('mobile-nav').classList.toggle('hidden')"
                    class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-nav" class="hidden md:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}"
                class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Dashboard</a>
            @if (!auth()->user()->isKepalaBps())
                <a href="{{ route('laporan.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Laporan
                    Saya</a>
            @endif
            @if (auth()->user()->role === 'staf')
                <a href="{{ route('staf.tugas.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Tugas
                    Saya</a>
            @endif
            @if (auth()->user()->isKepalaBagian())
                <a href="{{ route('kabag.tugas.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Tugas
                    Tim</a>
                <a href="{{ route('kabag.approval.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Persetujuan</a>
            @endif
            @if (auth()->user()->isKepalaBps())
                <a href="{{ route('kepala-bps.rekap') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Rekap
                    Kantor</a>
                <a href="{{ route('kepala-bps.tugas.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Beri
                    Tugas</a>
                <a href="{{ route('kepala-bps.approval.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Persetujuan</a>
            @endif
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Kelola
                    User</a>
                <a href="{{ route('admin.bagian.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Kelola
                    Bagian</a>
            @endif
            <hr class="border-white/10 my-2">
            <a href="{{ route('profile.edit') }}"
                class="block px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10">Profile
                Saya</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="block w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-400 hover:text-red-300 hover:bg-white/10">Keluar</button>
            </form>
        </div>
    </div>
</nav>
