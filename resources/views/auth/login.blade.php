<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
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

        <button type="submit"
                class="w-full bg-[#1F3864] hover:bg-[#16294a] text-white text-sm font-semibold py-2.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#1F3864]/30 focus:ring-offset-2">
            Masuk
        </button>
    </form>
</x-guest-layout>
