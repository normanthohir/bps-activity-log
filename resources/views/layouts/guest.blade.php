<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RAPI BPS Kota Ambon') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1F3864] via-[#2a4a7a] to-[#1a2d52] px-4">

        {{-- Decorative background circles --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/5 rounded-full"></div>
        </div>

        <div class="w-full max-w-md relative">
            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-[#1F3864] px-8 py-8 text-center">
                    <img src="{{ asset('images/bps.svg') }}" alt="Logo BPS"
                        class="w-20 h-20 mx-auto mb-4 brightness-0 invert">
                    <h1 class="text-white font-semibold text-lg">RAPI - BPS Kota Ambon</h1>
                    <p class="text-white/60 text-sm mt-1">Daily Activity Reporting System</p>
                </div>

                {{-- Form area --}}
                <div class="px-8 py-6">
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer --}}
            <p class="text-center text-white/40 text-xs mt-6">Badan Pusat Statistik Kota Ambon &copy;
                {{ date('Y') }}</p>
        </div>
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
        });
    </script>
</body>

</html>
