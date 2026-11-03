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

            {{-- Penanda mode kerja (hanya Kepala Tim) --}}
            @if ($user->role === 'kepala_bagian')
                <span
                    class="hidden items-center gap-1.5 rounded-full bg-[#DCE6F1] px-3 py-1.5 text-xs font-semibold text-[#1F3864] md:inline-flex">
                    <span
                        class="h-1.5 w-1.5 rounded-full {{ \App\Support\ModeKerja::ketuaAktif($user) ? 'bg-amber-500' : 'bg-sky-500' }}"></span>
                    {{ \App\Support\ModeKerja::ketuaAktif($user) ? 'Mode Ketua Tim' : 'Mode Anggota' }}
                </span>
            @endif

            {{-- Lonceng notifikasi (hanya mode Anggota) --}}
            @if ($notifAnggota)
                @php
                    $n = $notifAnggota;
                    $baris = 'group flex items-center justify-between gap-3 px-5 py-3.5 transition-colors';
                @endphp

                <div x-data="{ open: false }" x-effect="document.body.classList.toggle('overflow-hidden', open)"
                    @keydown.escape.window="open = false">

                    <button type="button" @click="open = true" aria-haspopup="dialog"
                        aria-label="Pemberitahuan, {{ $n['total'] }} baru"
                        class="group relative rounded-xl p-2 text-gray-500 transition hover:bg-gray-100 hover:text-[#1F3864] active:scale-95 motion-reduce:transition-none">
                        <svg class="h-6 w-6 origin-top transition-transform duration-300 group-hover:rotate-12 motion-reduce:transition-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>

                        @if ($n['total'] > 0)
                            <span class="absolute right-0.5 top-0.5 flex h-[18px] min-w-[18px]">
                                @if ($n['mendesak'] > 0)
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-60 motion-reduce:animate-none"></span>
                                @endif
                                <span
                                    class="relative inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full px-1 text-[10px] font-bold tabular-nums text-white ring-2 ring-white {{ $n['mendesak'] > 0 ? 'bg-red-500' : 'bg-green-600' }}">
                                    {{ $n['total'] > 99 ? '99+' : $n['total'] }}
                                </span>
                            </span>
                        @endif
                    </button>

                    {{-- Modal. Memakai teleport agar tidak terpengaruh efek blur pada topbar --}}
                    <template x-teleport="body">
                        <div x-show="open" x-cloak role="dialog" aria-modal="true" aria-labelledby="judul-notif"
                            class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4">

                            <div x-show="open" x-transition.opacity @click="open = false"
                                class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

                            <div x-show="open" x-transition
                                class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl">

                                {{-- Kepala modal --}}
                                <div
                                    class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                                    <div>
                                        <h3 id="judul-notif" class="text-base font-semibold text-gray-900">Pemberitahuan
                                        </h3>
                                        <p class="text-xs text-gray-500">
                                            {{ $n['total'] > 0 ? $n['total'] . ' hal perlu perhatian Anda' : 'Tidak ada yang perlu ditindaklanjuti' }}
                                        </p>
                                    </div>
                                    <button type="button" @click="open = false" aria-label="Tutup"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="min-h-0 flex-1 overflow-y-auto">

                                    {{-- Kosong --}}
                                    @if ($n['total'] === 0)
                                        <div class="px-6 py-14 text-center">
                                            <span
                                                class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            </span>
                                            <p class="mt-3 text-sm font-medium text-gray-900">Semua beres</p>
                                            <p class="text-xs text-gray-500">Tidak ada laporan dikembalikan atau
                                                pekerjaan tertunda.</p>
                                        </div>
                                    @endif

                                    {{-- Laporan dikembalikan --}}
                                    @if ($n['dikembalikan']->isNotEmpty())
                                        <section>
                                            <h4
                                                class="sticky top-0 z-10 flex items-center gap-2 border-y border-red-100 bg-red-50 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-red-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                {{ $n['dikembalikan']->count() }} laporan dikembalikan
                                            </h4>
                                            <ul class="divide-y divide-gray-100">
                                                @foreach ($n['dikembalikan'] as $l)
                                                    <li class="flex items-start justify-between gap-3 px-5 py-3.5">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-medium text-gray-900">
                                                                {{ $l->tanggal->format('d M Y') }} &middot;
                                                                {{ Str::limit($l->uraian, 45) }}
                                                            </p>
                                                            <p class="mt-0.5 line-clamp-2 text-xs text-red-700">
                                                                Catatan: {{ $l->logApproval->last()?->catatan ?? '-' }}
                                                            </p>
                                                        </div>
                                                        <a href="{{ route('laporan.edit', $l) }}"
                                                            class="shrink-0 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700 active:scale-95">
                                                            Revisi
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </section>
                                    @endif

                                    {{-- Lewat tenggat --}}
                                    @if ($n['terlambat']->isNotEmpty())
                                        <section>
                                            <h4
                                                class="sticky top-0 z-10 flex items-center gap-2 border-y border-orange-100 bg-orange-50 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-orange-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                                {{ $n['terlambat']->count() }} pekerjaan lewat tenggat
                                            </h4>
                                            <ul class="divide-y divide-gray-100">
                                                @foreach ($n['terlambat'] as $t)
                                                    @php $hari = (int) abs($t->tenggat->copy()->startOfDay()->diffInDays(today())); @endphp
                                                    <li>
                                                        <a href="{{ route('pekerjaan.show', $t) }}"
                                                            class="{{ $baris }} hover:bg-orange-50/50">
                                                            <div class="min-w-0">
                                                                <p class="truncate text-sm font-medium text-gray-900">
                                                                    {{ $t->judul }}</p>
                                                                <p class="text-xs text-gray-500">
                                                                    Tenggat <span
                                                                        class="tabular-nums">{{ $t->tenggat->format('d M Y') }}</span>
                                                                    &middot;
                                                                    {{ $t->status === 'dikerjakan' ? 'Sedang dikerjakan' : 'Belum dikerjakan' }}
                                                                </p>
                                                            </div>
                                                            <span
                                                                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-600/20">
                                                                {{ $hari }} hari
                                                                <span
                                                                    class="transition-transform group-hover:translate-x-0.5 motion-reduce:transition-none">→</span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </section>
                                    @endif

                                    {{-- Selesai, belum dilaporkan --}}
                                    @if ($n['belum']->isNotEmpty())
                                        <section>
                                            <h4
                                                class="sticky top-0 z-10 flex items-center gap-2 border-y border-green-100 bg-green-50 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                                {{ $n['belum']->count() }} pekerjaan selesai, belum dilaporkan
                                            </h4>
                                            <ul class="divide-y divide-gray-100">
                                                @foreach ($n['belum'] as $t)
                                                    <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-medium text-gray-900">
                                                                {{ $t->judul }}</p>
                                                            <p class="text-xs text-gray-500">Selesai
                                                                {{ $t->updated_at->diffForHumans() }}</p>
                                                        </div>
                                                        <a href="{{ route('laporan.create', ['tugas' => $t->id]) }}"
                                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-green-700 active:scale-95 motion-reduce:transition-none">
                                                            <svg class="h-3.5 w-3.5" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor"
                                                                stroke-width="2.2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                                            </svg>
                                                            Buat laporan
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </section>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
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
