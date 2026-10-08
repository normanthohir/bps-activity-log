<section>
    <header class="mb-5">
        <p class="font-medium">Ubah Password</p>
        <p class="text-sm text-gray-500 mt-1">
            Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4" x-data="{ loading: false }"
        @submit="loading = true">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm text-gray-500 block mb-1">
                Password saat ini
            </label>
            <input id="update_password_current_password" name="current_password" type="password"
                class="w-full rounded-lg border-gray-300 @error('current_password', 'updatePassword') border-red-500 @enderror"
                autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="text-sm text-gray-500 block mb-1">
                Password baru
            </label>
            <input id="update_password_password" name="password" type="password"
                class="w-full rounded-lg border-gray-300 @error('password', 'updatePassword') border-red-500 @enderror"
                autocomplete="new-password">
            @error('password', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm text-gray-500 block mb-1">
                Konfirmasi password baru
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="w-full rounded-lg border-gray-300 @error('password_confirmation', 'updatePassword') border-red-500 @enderror"
                autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" :disabled="loading"
                class="bg-blue-950 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg x-show="loading" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
            </button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
