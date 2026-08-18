{{-- resources/views/admin/bagian/index.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <p class="font-medium text-lg">Kelola Bagian/Seksi</p>
            <a href="{{ route('admin.bagian.create') }}" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg">
                + Tambah bagian
            </a>
        </div>

        <div class="bg-white rounded-xl border p-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2">Nama Bagian</th>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Kepala Bagian</th>
                        <th class="pb-2">Jumlah Pegawai</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bagian as $item)
                        <tr class="border-t">
                            <td class="py-2">{{ $item->nama_bagian }}</td>
                            <td class="py-2">{{ $item->kode_bagian }}</td>
                            <td class="py-2">{{ $item->kepalaBagian?->name ?? '- belum ditentukan -' }}</td>
                            <td class="py-2">{{ $item->pegawai_count }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('admin.bagian.edit', $item) }}" class="underline">Edit</a>
                                <form method="POST" action="{{ route('admin.bagian.destroy', $item) }}" class="inline"
                                      onsubmit="return confirm('Hapus bagian ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
