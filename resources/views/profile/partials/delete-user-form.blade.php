<section>
    <header class="mb-5">
        <p class="font-medium text-red-600">Hapus Akun</p>
        <p class="text-sm text-gray-500 mt-1">
            Setelah akun dihapus, semua data terkait akan dihapus permanen. Unduh data yang ingin Anda simpan sebelum melanjutkan.
        </p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded-lg border border-red-200 hover:bg-red-100">
        Hapus Akun
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <p class="font-medium text-lg mb-1">Yakin ingin menghapus akun ini?</p>
            <p class="text-sm text-gray-500 mb-4">
                Setelah dihapus, semua data akan hilang permanen. Masukkan password Anda untuk konfirmasi.
            </p>

            <div class="mb-6">
                <label for="password" class="sr-only">Password</label>
                <input id="password" name="password" type="password" placeholder="Password"
                       class="w-full rounded-lg border-gray-300 @error('password', 'userDeletion') border-red-500 @enderror">
                @error('password', 'userDeletion')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close')"
                        class="text-sm px-4 py-2 rounded-lg border">
                    Batal
                </button>
                <button type="submit"
                        class="bg-red-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-red-700">
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>