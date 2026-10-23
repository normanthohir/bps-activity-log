{{-- resources/views/profile/edit.blade.php --}}
@php
    $prefix = match ($user->role) {
        'kepala_bagian' => 'kabag.',
        'kepala_bps' => 'kepala-bps.',
        'admin' => 'admin.',
        default => '', // staf
    };

    $namaRole = match ($user->role) {
        'kepala_bagian' => 'Kepala Tim',
        'kepala_bps' => 'Kepala BPS Kota',
        'admin' => 'Admin',
        default => 'Staf',
    };

    $roleGaya = match ($user->role) {
        'kepala_bagian' => 'bg-green-50 text-green-700 ring-green-600/20',
        'kepala_bps' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'admin' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        default => 'bg-blue-50 text-blue-700 ring-blue-600/20',
    };

    $user->loadMissing(['bagian', 'atasan']);

    // Khusus kepala tim: jumlah anggota timnya (relasi bawahan dari Model User)
    $jumlahAnggota = $user->role === 'kepala_bagian' ? $user->bawahan()->count() : null;
@endphp

<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Profil Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi akun dan keamanan Anda.</p>
        </div>

        {{-- Kartu identitas (data dari database, tidak bisa diubah di sini) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="h-16 bg-gradient-to-r from-[#1F3864] to-[#2d5191]"></div>

            <div class="px-6 pb-6">
                <div class="-mt-8 flex flex-col sm:flex-row sm:items-end gap-4">
                    <span class="w-16 h-16 rounded-full bg-white ring-4 ring-white shadow-sm flex items-center justify-center shrink-0">
                        <span class="w-full h-full rounded-full bg-[#DCE6F1] text-[#1F3864] text-2xl font-semibold flex items-center justify-center">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </span>
                    <div class="min-w-0 sm:pb-1">
                        <p class="text-lg font-semibold text-white truncate">{{ $user->name }}</p>
                        <span class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-full ring-1 ring-inset {{ $roleGaya }}">
                            {{ $namaRole }}
                        </span>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 mt-6 pt-5 border-t border-gray-100">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">NIP</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $user->nip ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bagian</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $user->bagian?->nama_bagian ?? ($user->role === 'kepala_bps' ? 'Seluruh bagian' : '-') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Atasan langsung</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $user->atasan?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Status email</dt>
                        <dd class="mt-1">
                            @if ($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                    Belum diverifikasi
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if (! is_null($jumlahAnggota))
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Anggota tim</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $jumlahAnggota }} orang</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Terdaftar sejak</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $user->created_at->translatedFormat('d F Y') }}</dd>
                    </div>
                </dl>

                <p class="text-xs text-gray-400 mt-5">
                    NIP, bagian, dan atasan diatur oleh Admin. Hubungi Admin jika ada data yang perlu diperbaiki.
                </p>
            </div>
        </div>

        {{-- Form informasi akun --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            @include('profile.partials.update-profile-information-form', ['prefix' => $prefix])
        </div>

        {{-- Form ubah password --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            @include('profile.partials.update-password-form')
        </div>

    </div>
</x-app-layout>