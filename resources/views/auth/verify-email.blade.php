<x-guest-layout>
    {{-- Ikon amplop --}}
    <div class="flex justify-center mb-4">
        <span class="w-14 h-14 rounded-full bg-[#DCE6F1] text-[#1F3864] flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
        </span>
    </div>

    <div class="text-center mb-5">
        <h1 class="text-lg font-semibold text-gray-900">Verifikasi email Anda</h1>
        <p class="text-sm text-gray-600 mt-2">
            Kami telah mengirim tautan verifikasi ke
            <span class="font-medium text-gray-900">{{ auth()->user()->email }}</span>.
            Buka email tersebut dan klik tautannya untuk mengaktifkan akun.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-3 py-2.5 rounded-lg">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <p class="text-xs text-gray-500 text-center mb-4">
        Belum menerima email? Periksa folder Spam, atau kirim ulang dengan tombol di bawah.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto"
              x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <x-submit-button class="w-full sm:w-auto" loadingText="Mengirim...">
                Kirim ulang email verifikasi
            </x-submit-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout> 