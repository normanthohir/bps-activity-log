<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                autocomplete="username"
                class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                placeholder="contoh: nama@bps.go.id">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="mb-5">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864]"
                placeholder="Masukkan password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <x-submit-button  class="w-full">
            Masuk
        </x-submit-button>

        <div class="flex items-center justify-between mt-2">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

    </form>
</x-guest-layout>
