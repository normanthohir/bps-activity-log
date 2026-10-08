<x-guest-layout>
    <div class="mb-4">
        <p class="font-medium text-gray-900">Buat Password Baru</p>
        <p class="text-sm text-gray-600 mt-1">
            Masukkan password baru untuk akun Anda. Gunakan minimal 8 karakter.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}"
          x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <!-- Token reset password -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div>
            <label for="email" class="text-sm text-gray-500 block mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="w-full rounded-lg border-gray-300 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password baru -->
        <div class="mt-4">
            <label for="password" class="text-sm text-gray-500 block mb-1">Password baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded-lg border-gray-300 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi password baru -->
        <div class="mt-4">
            <label for="password_confirmation" class="text-sm text-gray-500 block mb-1">
                Konfirmasi password baru
            </label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password"
                   class="w-full rounded-lg border-gray-300 @error('password_confirmation') border-red-500 @enderror">
            @error('password_confirmation')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-5">
            <x-submit-button loadingText="Menyimpan...">Reset Password</x-submit-button>
        </div>
    </form>
</x-guest-layout>