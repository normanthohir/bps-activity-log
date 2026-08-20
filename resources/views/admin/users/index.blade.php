{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Kelola Akun Pegawai</h1>
                <p class="text-sm text-gray-500 mt-0.5">Semua pengguna sistem beserta role dan bagiannya.</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center gap-1.5 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Akun
            </a>
        </div>

        {{-- Search & filter --}}
        <form method="GET" class="flex flex-wrap items-center gap-3 mb-5">
            <div class="relative flex-1 min-w-0 sm:min-w-[200px]">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..."
                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
            </div>
            <select name="role" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua Role</option>
                <option value="staf" @selected(request('role') === 'staf')>Staf</option>
                <option value="kepala_bagian" @selected(request('role') === 'kepala_bagian')>Kepala Bagian</option>
                <option value="kepala_bps" @selected(request('role') === 'kepala_bps')>Kepala BPS</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            </select>
            <button type="submit" class="bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Terapkan</button>
            @if (request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>

        {{-- Table card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($users->isEmpty())
                {{-- Empty state --}}
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    @if (request('search') || request('role'))
                        <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada akun yang cocok</p>
                        <p class="text-sm text-gray-500 mt-1">Coba ubah kata kunci pencarian atau filter role.</p>
                        <a href="{{ route('admin.users.index') }}" class="inline-block mt-4 text-sm text-[#1F3864] font-medium hover:underline">
                            Reset pencarian
                        </a>
                    @else
                        <p class="text-sm font-medium text-gray-900 mt-3">Belum ada akun pegawai</p>
                        <p class="text-sm text-gray-500 mt-1">Tambahkan akun pertama untuk mulai mengelola pengguna sistem.</p>
                        <a href="{{ route('admin.users.create') }}"
                           class="inline-flex items-center gap-1.5 mt-4 bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            + Tambah Akun
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 font-medium">Nama</th>
                            <th class="py-3 px-5 font-medium">NIP</th>
                            <th class="py-3 px-5 font-medium">Role</th>
                            <th class="py-3 px-5 font-medium">Bagian</th>
                            <th class="py-3 px-5 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            @php
                                $roleStyles = [
                                    'admin' => 'bg-red-50 text-red-700',
                                    'kepala_bps' => 'bg-purple-50 text-purple-700',
                                    'kepala_bagian' => 'bg-blue-50 text-blue-700',
                                    'staf' => 'bg-gray-100 text-gray-600',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs font-semibold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 font-mono text-xs text-gray-600">{{ $user->nip }}</td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-md {{ $roleStyles[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-gray-700">{{ $user->bagian?->nama_bagian ?? '—' }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="text-gray-600 hover:text-[#1F3864] text-sm font-medium transition-colors">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" data-confirm="Hapus akun {{ $user->name }}? Tindakan ini tidak bisa dibatalkan."
                                                    class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors">
                                                Hapus
                                            </button>
                                        </form>
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