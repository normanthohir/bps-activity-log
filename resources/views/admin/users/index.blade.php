{{-- resources/views/admin/users/index.blade.php --}}
@php
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

    $adaFilter =
        request()->filled('search') ||
        request()->filled('role') ||
        request()->filled('bagian') ||
        request()->filled('status');
@endphp

<x-app-layout>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="daftarUser()">


        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Kelola Akun Pegawai</h1>
                <p class="text-sm text-gray-500 mt-1">Semua pengguna sistem beserta peran dan bagiannya.</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Akun
            </a>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Total akun</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $ringkasan['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Staf</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $ringkasan['staf'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Kepala tim</p>
                <p class="text-2xl font-semibold text-blue-700 mt-1">{{ $ringkasan['kepala_tim'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Belum verifikasi email</p>
                <p
                    class="text-2xl font-semibold {{ $ringkasan['belum_verifikasi'] ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                    {{ $ringkasan['belum_verifikasi'] }}
                </p>
            </div>
        </div>

        {{-- Pencarian dan filter --}}
        <form method="GET" action="{{ route('admin.users.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-3 mb-4 flex flex-col lg:flex-row gap-3">

            <div class="relative flex-1 min-w-0">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                    placeholder="Cari nama, NIP, atau email..."
                    oninput="clearTimeout(window.searchTimer); window.searchTimer = setTimeout(() => this.form.submit(), 500)"
                    @if (request()->filled('search'))
                autofocus onfocus="this.setSelectionRange(this.value.length, this.value.length)"
                @endif
                class="w-full pl-9 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 lg:flex lg:items-center">
                <select name="role" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    <option value="">Semua role</option>
                    <option value="staf" @selected(request('role') === 'staf')>Staf</option>
                    <option value="kepala_bagian" @selected(request('role') === 'kepala_bagian')>Kepala Tim</option>
                    <option value="kepala_bps" @selected(request('role') === 'kepala_bps')>Kepala BPS</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                </select>

                <select name="bagian" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    <option value="">Semua bagian</option>
                    @foreach ($daftarBagian as $b)
                        <option value="{{ $b->id }}" @selected((string) request('bagian') === (string) $b->id)>
                            {{ $b->nama_bagian }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                    <option value="">Semua status</option>
                    <option value="terverifikasi" @selected(request('status') === 'terverifikasi')>Terverifikasi</option>
                    <option value="belum" @selected(request('status') === 'belum')>Belum verifikasi</option>
                </select>

                @if ($adaFilter)
                    <a href="{{ route('admin.users.index') }}"
                        class="text-sm text-gray-500 hover:text-gray-800 px-2 whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Hasil (bagian ini yang diganti otomatis saat mencari/memfilter) --}}
        <div id="hasil-user" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($users->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    @if ($adaFilter)
                        <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada akun yang cocok</p>
                        <p class="text-sm text-gray-500 mt-1">Coba ubah kata kunci atau filter yang dipakai.</p>
                    @else
                        <p class="text-sm font-medium text-gray-900 mt-3">Belum ada akun pegawai</p>
                        <p class="text-sm text-gray-500 mt-1">Tambahkan akun pertama untuk mulai mengelola pengguna.
                        </p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Pegawai</th>
                                <th class="py-3 px-3 font-medium hidden lg:table-cell">NIP</th>
                                <th class="py-3 px-3 font-medium">Role</th>
                                <th class="py-3 px-3 font-medium hidden md:table-cell">Bagian</th>
                                <th class="py-3 px-3 font-medium">Status</th>
                                <th class="py-3 px-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-900 truncate">
                                                    {{ $user->name }}
                                                    @if ($user->id === auth()->id())
                                                        <span
                                                            class="ml-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1F3864]/10 text-[#1F3864] align-middle">Anda</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                {{-- Di layar kecil, NIP dan bagian pindah ke sini --}}
                                                <p class="text-xs text-gray-400 truncate lg:hidden">
                                                    {{ $user->nip ?: '-' }}
                                                    <span class="md:hidden">&middot;
                                                        {{ $user->bagian?->nama_bagian ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-3 font-mono text-xs text-gray-600 hidden lg:table-cell">
                                        {{ $user->nip ?: '—' }}
                                    </td>

                                    <td class="py-3.5 px-3">
                                        <span
                                            class="inline-flex text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset {{ $roleGaya[$user->role] ?? $roleGaya['staf'] }}">
                                            {{ $roleNama[$user->role] ?? $user->role }}
                                        </span>
                                    </td>

                                    <td class="py-3.5  text-gray-700 hidden md:table-cell">
                                        {{ $user->bagian?->nama_bagian ?? '—' }}
                                    </td>

                                    <td class="py-3.5 px-3">
                                        @if ($user->hasVerifiedEmail())
                                            <span
                                                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-50 text-green-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                Terverifikasi
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Belum verifikasi
                                            </span>

                                        @endif
                                    </td>

                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center justify-end gap-2 relative">
                                            <x-dropdown-action>
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                                    Edit
                                                </a>
                                                @if ($user->id !== auth()->id())
                                                    <form method="POST"
                                                        action="{{ route('admin.users.destroy', $user) }}">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            data-confirm="Hapus akun {{ $user->name }}? Tindakan ini tidak bisa dibatalkan."
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (!$user->hasVerifiedEmail())
                                                    <form method="POST"
                                                        action="{{ route('admin.users.resend-verification', $user) }}"
                                                        class="mt-1">
                                                        @csrf
                                                        <button
                                                            class="w-full text-left px-4 py-2 text-sm text-amber-700 hover:bg-amber-50 transition-colors">
                                                            Kirim ulang verifikasi
                                                        </button>
                                                    </form>
                                                @endif
                                            </x-dropdown-action>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>


</x-app-layout>
