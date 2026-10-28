<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RAPI - BPS Kota Ambon') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- sidebarOpen dipakai oleh sidebar (layouts/navigation) dan tombol hamburger di bawah --}}
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }"
    :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }">

    @include('layouts.navigation')

    {{-- Area konten: bergeser sebesar lebar sidebar di layar besar --}}
    <div class="lg:pl-64 min-h-screen flex flex-col">

        {{-- Bar atas khusus mobile (tombol hamburger) --}}
        <div class="lg:hidden sticky top-0 z-30 h-14 flex items-center gap-3 bg-white border-b border-gray-200 px-4">
            <button @click="sidebarOpen = true" aria-label="Buka menu"
                class="text-gray-600 hover:text-gray-900 p-1.5 -ml-1.5 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <span class="font-semibold text-sm text-[#1F3864]">RAPI - BPS Kota Ambon</span>
        </div>

        @isset($header)
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main id="main-content" class="relative flex-1">

            {{-- Kerangka shimmer: tampil sekilas saat berpindah halaman --}}
            <div id="page-shimmer" class="hidden max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="animate-pulse bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-3">
                    <div class="h-5 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-10 bg-gray-200 rounded w-full"></div>
                </div>
                <div class="animate-pulse bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-6"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                </div>
            </div>

            <div id="actual-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Pesan flash (SweetAlert2) ─────────────────────────
            @if (session('success'))
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: @json(session('success')),
                    timer: 3000, timerProgressBar: true, showConfirmButton: false,
                    customClass: { popup: 'swal2-popup-bootstrap' }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error', title: 'Gagal!', text: @json(session('error')),
                    timer: 3000, timerProgressBar: true, showConfirmButton: false,
                    customClass: { popup: 'swal2-popup-bootstrap' }
                });
            @endif

            // ── Konfirmasi hapus ──────────────────────────────────
            // Memakai event delegation: tetap bekerja untuk tombol yang muncul belakangan
            // (misalnya hasil pencarian otomatis), bukan hanya yang ada saat halaman dibuka.
            document.addEventListener('click', function (e) {
                const el = e.target.closest('[data-confirm]');
                if (!el) return;

                e.preventDefault();
                const form = el.closest('form');
                if (!form) return;

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: el.getAttribute('data-confirm'),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1F3864',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: el.dataset.confirmOk || 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            });

            // ── Shimmer saat berpindah halaman ────────────────────
            const shimmer = document.getElementById('page-shimmer');
            const konten = document.getElementById('actual-content');
            let pemulih = null;

            // Tautan yang hanya mengunduh berkas (halaman tidak berpindah) tidak boleh memicu shimmer.
            // Untuk tautan unduhan lain, cukup beri atribut data-no-shimmer.
            const POLA_UNDUHAN = /(export|template-excel|\/berkas\/)/i;

            function tampilkan() {
                konten.classList.add('hidden');
                shimmer.classList.remove('hidden');
                clearTimeout(pemulih);
                // Pengaman: kalau ternyata tidak ada perpindahan halaman, kembalikan tampilan
                pemulih = setTimeout(pulihkan, 8000);
            }

            function pulihkan() {
                clearTimeout(pemulih);
                shimmer.classList.add('hidden');
                konten.classList.remove('hidden');
            }

            document.addEventListener('click', function (e) {
                // Sudah ditangani skrip lain (misalnya paginasi AJAX atau konfirmasi hapus)
                if (e.defaultPrevented) return;
                if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                const link = e.target.closest('a[href]');
                if (!link) return;
                if (link.target && link.target !== '_self') return;
                if (link.hasAttribute('download') || link.hasAttribute('data-no-shimmer')) return;

                let url;
                try { url = new URL(link.href, window.location.href); } catch (err) { return; }

                if (url.origin !== window.location.origin) return;
                // Halaman yang sama (misalnya tautan #)
                if (url.pathname === window.location.pathname && url.search === window.location.search) return;
                if (POLA_UNDUHAN.test(url.pathname)) return;

                tampilkan();
            });

            // Tombol "Kembali" di browser: jangan tertahan di kondisi shimmer
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) pulihkan();
            });
        });
    </script>
</body>

</html>