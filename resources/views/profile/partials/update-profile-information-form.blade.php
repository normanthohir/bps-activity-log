<section>
    <header class="mb-5">
        <p class="font-semibold text-gray-900">Informasi Akun</p>
        <p class="text-sm text-gray-500 mt-1">Perbarui nama dan alamat email Anda.</p>
    </header>

    {{-- Form kecil untuk kirim ulang email verifikasi --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4"
        x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="text-sm font-medium text-gray-700 block mb-1.5">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('name') border-red-500 @else border-gray-300 @enderror"
                required  autocomplete="name">
            @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="text-sm font-medium text-gray-700 block mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                class="w-full rounded-lg text-sm focus:ring-[#1F3864]/20 focus:border-[#1F3864] @error('email') border-red-500 @else border-gray-300 @enderror"
                required autocomplete="username">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <p class="text-xs text-gray-400 mt-1.5">
                Jika email diganti, Anda perlu memverifikasi email baru lewat tautan yang kami kirim.
            </p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5">
                    <p class="text-sm text-amber-800">
                        Email Anda belum diverifikasi.
                        <button type="submit" form="send-verification"
                            class="underline font-medium hover:text-amber-900">
                            Kirim ulang email verifikasi
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 text-sm text-green-700 font-medium">
                            Tautan verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-submit-button loadingText="Menyimpan...">Simpan perubahan</x-submit-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-green-600 font-medium">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>