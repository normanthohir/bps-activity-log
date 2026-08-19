{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Edit Akun: {{ $user->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi akun pegawai.</p>
        </div>

        {{-- Form card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('nip') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]" required>
                        @foreach (['staf' => 'Staf', 'kepala_bagian' => 'Kepala Bagian', 'kepala_bps' => 'Kepala BPS Kota', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Bagian</label>
                    <select name="bagian_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Tidak ada (untuk Kepala BPS/Admin) -</option>
                        @foreach ($daftarBagian as $bagian)
                            <option value="{{ $bagian->id }}" @selected(old('bagian_id', $user->bagian_id) == $bagian->id)>{{ $bagian->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Atasan Langsung</label>
                    <select name="atasan_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                        <option value="">- Tidak ada -</option>
                        @foreach ($calonAtasan as $atasan)
                            <option value="{{ $atasan->id }}" @selected(old('atasan_id', $user->atasan_id) == $atasan->id)>{{ $atasan->name }} ({{ ucwords(str_replace('_',' ',$atasan->role)) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-5 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Reset Password --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-6">
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-1">Reset Password</h2>
                <p class="text-sm text-gray-500 mb-4">Reset password user ke default: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">password</code></p>
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <button type="submit" data-confirm="Reset password {{ $user->name }} ke default?"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
