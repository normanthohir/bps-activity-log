<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}"
          x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="text-sm text-gray-500 block mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus
                   class="w-full rounded-lg border-gray-300 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label for="password" class="text-sm text-gray-500 block mb-1">Password baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded-lg border-gray-300 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label for="password_confirmation" class="text-sm text-gray-500 block mb-1">Konfirmasi password baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full rounded-lg border-gray-300">
        </div>

        <div class="flex justify-end mt-4">
            <x-submit-button loadingText="Menyimpan...">Reset Password</x-submit-button>
        </div>
    </form>
</x-guest-layout>