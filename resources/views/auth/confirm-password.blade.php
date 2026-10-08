<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Ini adalah area yang aman. Silakan konfirmasi password Anda sebelum melanjutkan.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}"
          x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div>
            <label for="password" class="text-sm text-gray-500 block mb-1">Password</label>
            <input id="password" name="password" type="password" required autofocus
                   autocomplete="current-password"
                   class="w-full rounded-lg border-gray-300 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end mt-4">
            <x-submit-button loadingText="Memeriksa...">Konfirmasi</x-submit-button>
        </div>
    </form>
</x-guest-layout>