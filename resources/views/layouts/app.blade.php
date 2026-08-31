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

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/boostrap.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Ditambahkan atribut id="main-content" untuk kontrol transisi -->
        <main id="main-content" class="relative">
            
            <!-- 1. BLOK TEMPLATE SHIMMER (SKELETON LOADING) -->
            <div id="page-shimmer" class="hidden max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Baris Shimmer Atas (Form / Judul Kecil) -->
                <div class="animate-pulse bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-3">
                    <div class="h-5 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-10 bg-gray-200 rounded w-full"></div>
                </div>
                <!-- Baris Shimmer Bawah (Tabel / Grid Data) -->
                <div class="animate-pulse bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-6"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                </div>
            </div>

            <!-- 2. KONTEN UTAMA ASLI -->
            <div id="actual-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- SweetAlert2: auto flash message + confirm delete --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{!! session('success') !!}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-popup-bootstrap'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{!! session('error') !!}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-popup-bootstrap'
                    }
                });
            @endif

            document.querySelectorAll('[data-confirm]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    var form = this.closest('form');
                    var message = this.getAttribute('data-confirm');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#1F3864',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // ==========================================
            // 3. JAVASCRIPT PEMICU LOADING SHIMMER
            // ==========================================
            const shimmer = document.getElementById('page-shimmer');
            const actualContent = document.getElementById('actual-content');

            // Deteksi semua klik pada tag anchor <a>
            document.querySelectorAll('a').forEach(function(link) {
                // Validasi agar shimmer hanya berjalan jika link mengarah ke internal domain yang sama
                // dan tidak mengaktifkan aksi seperti download, hash (#), JavaScript void, atau tombol logout
                if (
                    link.href && 
                    link.href.startsWith(window.location.origin) && 
                    !link.getAttribute('target') &&
                    !link.href.includes('#') &&
                    !link.classList.contains('logout-button') && // jika ada class khusus logout, lewatkan
                    link.getAttribute('href') !== 'javascript:void(0);'
                ) {
                    link.addEventListener('click', function(e) {
                        // Jangan picu shimmer jika user melakukan klik kanan / ctrl + klik (open in new tab)
                        if (e.metaKey || e.ctrlKey || e.shiftKey) return;

                        // Sembunyikan konten asli, ganti dengan kerangka shimmer
                        actualContent.classList.add('hidden');
                        shimmer.classList.remove('hidden');
                    });
                }
            });

            // Antisipasi jika user menekan tombol 'Back' di browser agar halaman lama tidak membeku dalam kondisi shimmer
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    shimmer.classList.add('hidden');
                    actualContent.classList.remove('hidden');
                }
            });
        });
    </script>
</body>

</html>
