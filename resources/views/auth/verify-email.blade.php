<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Lupa password? Masukkan email akun Anda, dan kami akan mengirim
        tautan untuk membuat password baru.
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}"
          x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div>
            <label for="email" class="text-sm text-gray-500 block mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border-gray-300 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
                Kembali ke login
            </a>
            <x-submit-button loadingText="Mengirim...">Kirim Tautan Reset</x-submit-button>
        </div>
    </form>
</x-guest-layout>