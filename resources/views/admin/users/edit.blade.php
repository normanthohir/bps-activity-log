<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @include('admin.users.partials.form-akun', [
            'akun' => $user,
            'action' => route('admin.users.update', $user),
            'judul' => 'Edit Akun: ' . $user->name,
            'deskripsi' => 'Perbarui informasi akun pegawai.',
            'tombol' => 'Simpan Perubahan',
        ])
        @if ($user->id !== auth()->id())
            <div class="animate-in delay-2 mt-5 overflow-hidden rounded-2xl border border-amber-200 bg-white">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Reset password</h2>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Mengembalikan password {{ $user->name }} ke default (<strong>password</strong>).
                                Berlaku untuk semua jenis akun. Beri tahu pegawai agar segera mengubahnya.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="shrink-0">
                        @csrf
                        <button type="button"
                            data-confirm="Password {{ $user->name }} akan dikembalikan ke default (password)."
                            data-confirm-ok="Ya, reset"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:-translate-y-0.5 hover:bg-amber-100 active:scale-95 motion-reduce:transition-none sm:w-auto">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reset ke default
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
