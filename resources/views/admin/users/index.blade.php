{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <p class="font-medium text-lg">Kelola Akun Pegawai</p>
            <a href="{{ route('admin.users.create') }}" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Tambah akun
            </a>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">NIP</th>
                        <th class="pb-2">Role</th>
                        <th class="pb-2">Bagian</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="py-2">{{ $user->name }}</td>
                            <td class="py-2">{{ $user->nip }}</td>
                            <td class="py-2">{{ ucwords(str_replace('_', ' ', $user->role)) }}</td>
                            <td class="py-2">{{ $user->bagian?->nama_bagian ?? '-' }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('admin.users.edit', $user) }}" class="underline">Edit</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                      onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $users->links() }}
        </div>

    </div>
</x-app-layout>
