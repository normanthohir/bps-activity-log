<section>
    <header class="mb-5">
        <p class="font-medium">Informasi Profil</p>
        <p class="text-sm text-gray-500 mt-1">
            Perbarui nama dan alamat email akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- <form method="post" action="{{ route('profile.update') }}" x-ref="form" novalidate class="space-y-4"> --}}
    <form method="post" action="{{ route('profile.update') }}" class="space-y-4" x-data="{ loading: false }"
        @submit="loading = true">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="text-sm text-gray-500 block mb-1">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                class="w-full rounded-lg border-gray-300 @error('name') border-red-500 @enderror" required autofocus
                autocomplete="name">
            @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nip" class="text-sm text-gray-500 block mb-1">NIP</label>
            <input id="nip" name="nip" type="text" value="{{ old('nip', $user->nip) }}"
                class="w-full rounded-lg border-gray-300 @error('nip') border-red-500 @enderror" autocomplete="off">
            @error('nip')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="text-sm text-gray-500 block mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                class="w-full rounded-lg border-gray-300 @error('email') border-red-500 @enderror" required
                autocomplete="username">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-600">
                        Alamat email Anda belum diverifikasi.
                        <button form="send-verification" class="underline hover:text-gray-900">
                            Klik di sini untuk kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-600 font-medium">
                            Tautan verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
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

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
