<section>
    <header class="mb-5">
        <p class="font-semibold text-gray-900">Ubah Password</p>
        <p class="text-sm text-gray-500 mt-1">
            Gunakan password minimal 8 karakter yang sulit ditebak agar akun Anda tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4"
        x-data="{ loading: false, show: false }" @submit="loading = true">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm font-medium text-gray-700 block mb-1.5">
                Password saat ini
            </label>
            <input id="update_password_current_password" name="current_password"
                :type="show ? 'text' : 'password'" autocomplete="current-password"
                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('current_password', 'updatePassword') border-red-500 @else border-gray-300 @enderror">
            @error('current_password', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="text-sm font-medium text-gray-700 block mb-1.5">
                Password baru
            </label>
            <input id="update_password_password" name="password"
                :type="show ? 'text' : 'password'" autocomplete="new-password"
                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('password', 'updatePassword') border-red-500 @else border-gray-300 @enderror">
            @error('password', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm font-medium text-gray-700 block mb-1.5">
                Konfirmasi password baru
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation"
                :type="show ? 'text' : 'password'" autocomplete="new-password"
                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('password_confirmation', 'updatePassword') border-red-500 @else border-gray-300 @enderror">
            @error('password_confirmation', 'updatePassword')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
            <input type="checkbox" x-model="show"
                class="rounded border-gray-300 text-[#1F3864] focus:ring-[#1F3864]/30">
            Tampilkan password
        </label>

        <div class="flex items-center gap-3 pt-2">
            <x-submit-button loadingText="Menyimpan...">Ubah password</x-submit-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-green-600 font-medium">Password berhasil diubah.</p>
            @endif
        </div>
    </form>
</section>