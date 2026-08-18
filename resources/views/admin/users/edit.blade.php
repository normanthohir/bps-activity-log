{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <div class="bg-white rounded-xl border p-6">
            <p class="font-medium text-lg mb-5">Edit Akun: {{ $user->name }}</p>

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Nama lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('nip') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-lg border-gray-300" required>
                    @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Role</label>
                    <select name="role" class="w-full rounded-lg border-gray-300" required>
                        @foreach (['staf' => 'Staf', 'kepala_bagian' => 'Kepala Bagian', 'kepala_bps' => 'Kepala BPS Kota', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500 block mb-1">Bagian</label>
                    <select name="bagian_id" class="w-full rounded-lg border-gray-300">
                        <option value="">- Tidak ada (untuk Kepala BPS/Admin) -</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}" @selected(old('bagian_id', $user->bagian_id) == $bagian->id)>
                                {{ $bagian->nama_bagian }}
                            </option>
                        @endforeach
                    </select>
                    @error('bagian_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="text-sm text-gray-500 block mb-1">Atasan langsung</label>
                    <select name="atasan_id" class="w-full rounded-lg border-gray-300">
                        <option value="">- Tidak ada -</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}" @selected(old('atasan_id', $user->atasan_id) == $atasan->id)>
                                {{ $atasan->name }} ({{ ucwords(str_replace('_',' ',$atasan->role)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-gray-900 text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
