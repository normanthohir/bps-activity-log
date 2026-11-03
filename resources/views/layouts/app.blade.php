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

{{-- State bersama: sidebarOpen (drawer mobile) dan collapsed (rail ikon di desktop).
     Dipakai oleh layouts.navigation dan layouts.topbar. --}}
<body class="font-sans antialiased bg-gray-50"
    x-data="{
        sidebarOpen: false,
        collapsed: (() => { try { return localStorage.getItem('sidebar-collapsed') === '1' } catch (e) { return false } })(),
        toggle() {
            this.collapsed = !this.collapsed;
            try { localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0') } catch (e) {}
        }
    }"
    :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }">

    @include('layouts.navigation')

    {{-- Area konten: bergeser sebesar lebar sidebar di layar besar, ikut menyusut saat sidebar diciutkan --}}
    <div class="flex min-h-screen flex-col transition-[padding] duration-300 ease-out motion-reduce:transition-none lg:pl-64"
        :class="collapsed ? 'lg:!pl-[76px]' : ''">

        @include('layouts.topbar')

        @isset($header)
            <header class="border-b border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
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
            // Memakai event delegation: tetap bekerja untuk tombol yang muncul belakangan.
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

            // Tautan yang hanya mengunduh berkas tidak boleh memicu shimmer.
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
                if (e.defaultPrevented) return;
                if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                const link = e.target.closest('a[href]');
                if (!link) return;
                if (link.target && link.target !== '_self') return;
                if (link.hasAttribute('download') || link.hasAttribute('data-no-shimmer')) return;

                let url;
                try { url = new URL(link.href, window.location.href); } catch (err) { return; }

                if (url.origin !== window.location.origin) return;
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