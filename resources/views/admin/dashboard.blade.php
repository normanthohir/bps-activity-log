{{-- resources/views/admin/dashboard.blade.php --}}
@php
    $jam = now()->hour;
    $salam = match (true) {
        $jam < 11 => 'Selamat pagi',
        $jam < 15 => 'Selamat siang',
        $jam < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $roleGaya = [
        'admin' => 'bg-red-50 text-red-700 ring-red-600/20',
        'kepala_bps' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'kepala_bagian' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'staf' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
    $roleNama = [
        'admin' => 'Admin',
        'kepala_bps' => 'Kepala BPS',
        'kepala_bagian' => 'Kepala Tim',
        'staf' => 'Staf',
    ];

    // Dua angka ini opsional (diisi dari controller). Kalau belum ada, kartunya disembunyikan.
    $totalStaf = $totalStaf ?? null;
    $belumVerifikasi = $belumVerifikasi ?? null;

    // Untuk batang perbandingan jumlah pegawai antar bagian
    $maks = max(1, (int) $bagianList->max('pegawai_count'));

    // Bagian yang belum punya kepala tim
    $bagianTanpaKepala = $bagianList->filter(fn ($b) => ! $b->kepalaBagian)->values();

    $adaMasalah = ($belumVerifikasi ?? 0) > 0 || $bagianTanpaKepala->isNotEmpty();
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Sambutan + aksi cepat --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#1F3864] to-[#2d5191] p-6 sm:p-8 mb-6">
            <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/5"></div>
            <div class="absolute right-24 -bottom-16 w-40 h-40 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-sm text-white/70">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">
                        {{ $salam }}, {{ strtok(auth()->user()->name, ' ') }}
                    </h1>
                    <p class="text-sm text-white/70 mt-2 max-w-xl">
                        Ringkasan pengguna dan struktur bagian pada sistem RAPI BPS Kota Ambon.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center gap-1.5 bg-white text-[#1F3864] hover:bg-white/90 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah akun
                    </a>
                    <a href="{{ route('admin.bagian.create') }}"
                        class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-4 py-2.5 rounded-lg ring-1 ring-inset ring-white/25 transition-colors">
                        Tambah bagian
                    </a>
                </div>
            </div>
        </div>

        {{-- Kartu statistik --}}
        <div class="grid grid-cols-2 {{ $totalStaf !== null ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4 mb-6">

            <a href="{{ route('admin.users.index') }}"
                class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-[#1F3864]/40 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-[#1F3864] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
                <p class="text-3xl font-semibold text-gray-900 mt-4">{{ $totalUser }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Total akun</p>
            </a>

            <a href="{{ route('admin.bagian.index') }}"
                class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-[#1F3864]/40 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                        </svg>
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-[#1F3864] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
                <p class="text-3xl font-semibold text-gray-900 mt-4">{{ $totalBagian }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Bagian / seksi</p>
            </a>

            <a href="{{ route('admin.users.index', ['role' => 'kepala_bagian']) }}"
                class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-[#1F3864]/40 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-[#1F3864] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
                <p class="text-3xl font-semibold text-gray-900 mt-4">{{ $totalKepalaBagian }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Kepala tim</p>
            </a>

            @if ($totalStaf !== null)
                <a href="{{ route('admin.users.index', ['role' => 'staf']) }}"
                    class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-[#1F3864]/40 hover:shadow-sm transition-all">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </span>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#1F3864] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                    <p class="text-3xl font-semibold text-gray-900 mt-4">{{ $totalStaf }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">Staf</p>
                </a>
            @endif
        </div>

        {{-- Perlu perhatian --}}
        <div class="mb-6 rounded-xl border {{ $adaMasalah ? 'border-amber-200 bg-amber-50/60' : 'border-green-200 bg-green-50/60' }} p-5">
            @if ($adaMasalah)
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm font-semibold text-amber-900">Perlu perhatian</p>
                </div>

                <ul class="space-y-2">
                    @if (($belumVerifikasi ?? 0) > 0)
                        <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                            <span class="text-amber-900">
                                <span class="font-semibold">{{ $belumVerifikasi }}</span> akun belum memverifikasi email.
                            </span>
                            <a href="{{ route('admin.users.index', ['status' => 'belum']) }}"
                                class="text-xs font-medium text-amber-800 hover:underline">Lihat daftar &rarr;</a>
                        </li>
                    @endif

                    @foreach ($bagianTanpaKepala as $b)
                        <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                            <span class="text-amber-900">
                                <span class="font-semibold">{{ $b->nama_bagian }}</span> belum memiliki kepala tim.
                            </span>
                            <a href="{{ route('admin.bagian.edit', $b) }}"
                                class="text-xs font-medium text-amber-800 hover:underline">Atur sekarang &rarr;</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-green-800">Semua beres. Tidak ada yang perlu ditindaklanjuti.</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

            {{-- Akun terbaru --}}
            <div class="xl:col-span-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="font-semibold text-gray-900">Akun terbaru</h2>
                        <p class="text-xs text-gray-500 mt-0.5">10 akun yang terakhir dibuat</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}"
                        class="text-sm font-medium text-[#1F3864] hover:underline">Lihat semua</a>
                </div>

                @if ($usersTerbaru->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-12">Belum ada akun.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                    <th class="py-2.5 px-5 font-medium">Pegawai</th>
                                    <th class="py-2.5 px-5 font-medium">Role</th>
                                    <th class="py-2.5 px-5 font-medium hidden sm:table-cell">Bagian</th>
                                    <th class="py-2.5 px-5 font-medium text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($usersTerbaru as $user)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3 px-5">
                                            <div class="flex items-center gap-3">
                                                <span class="w-8 h-8 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-5">
                                            <span class="inline-flex text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset {{ $roleGaya[$user->role] ?? $roleGaya['staf'] }}">
                                                {{ $roleNama[$user->role] ?? $user->role }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-gray-600 hidden sm:table-cell">
                                            {{ $user->bagian?->nama_bagian ?? '—' }}
                                        </td>
                                        <td class="py-3 px-5 text-right">
                                            @if ($user->hasVerifiedEmail())
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-green-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Terverifikasi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Belum verifikasi
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Pegawai per bagian --}}
            <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="font-semibold text-gray-900">Pegawai per bagian</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Termasuk kepala tim</p>
                    </div>
                    <a href="{{ route('admin.bagian.index') }}"
                        class="text-sm font-medium text-[#1F3864] hover:underline">Kelola</a>
                </div>

                @if ($bagianList->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-12">Belum ada bagian.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($bagianList as $bagian)
                            <li class="px-5 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $bagian->nama_bagian }}</p>
                                        <p class="text-xs mt-0.5 truncate {{ $bagian->kepalaBagian ? 'text-gray-500' : 'text-amber-600' }}">
                                            {{ $bagian->kepalaBagian ? 'Kepala tim: ' . $bagian->kepalaBagian->name : 'Belum ada kepala tim' }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $bagian->pegawai_count }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">{{ $bagian->kode_bagian }}</p>
                                    </div>
                                </div>
                                <div class="h-1.5 bg-gray-100 rounded-full mt-2.5 overflow-hidden">
                                    <div class="h-full rounded-full bg-[#1F3864]"
                                        style="width: {{ round(($bagian->pegawai_count / $maks) * 100) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>