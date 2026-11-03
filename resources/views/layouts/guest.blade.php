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

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes tamu-naik {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.98)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes tamu-melayang {

            0%,
            100% {
                transform: translate(0, 0)
            }

            50% {
                transform: translate(-14px, 18px)
            }
        }

        .tamu-naik {
            opacity: 0;
            animation: tamu-naik .6s cubic-bezier(.2, .7, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        .tamu-melayang {
            animation: tamu-melayang 9s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .tamu-naik {
                animation: none;
                opacity: 1;
            }

            .tamu-melayang {
                animation: none;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#1F3864] px-4 py-8"
        style="background-image: radial-gradient(120% 90% at 100% 0%, rgb(53 91 145 / .75), transparent 60%), radial-gradient(90% 70% at 0% 100%, rgb(26 45 82 / .9), transparent 60%)">

        {{-- Lingkaran dekoratif --}}
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="tamu-melayang absolute -right-40 -top-40 h-80 w-80 rounded-full bg-white/5"></div>
            <div class="tamu-melayang absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-white/5"
                style="animation-delay: -4s"></div>
            <div class="tamu-melayang absolute right-1/4 top-1/3 hidden h-24 w-24 rounded-full bg-white/5 sm:block"
                style="animation-delay: -2s"></div>
        </div>

        <div class="tamu-naik relative w-full max-w-md" style="--d:.05s">
            <div class="overflow-hidden rounded-2xl bg-white shadow-2xl shadow-black/30 ring-1 ring-white/10">

                {{-- Kepala kartu --}}
                <div class="relative bg-[#1F3864] px-6 py-8 text-center sm:px-8"
                    style="background-image: radial-gradient(120% 120% at 100% 0%, rgb(53 91 145 / .8), transparent 65%)">
                    <span
                        class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                        <img src="{{ asset('images/bps.svg') }}" alt="Logo BPS" class="h-12 w-12 brightness-0 invert">
                    </span>
                    <h1 class="text-lg font-semibold tracking-tight text-white">RAPI - BPS Kota Ambon</h1>
                    <p class="mt-1 text-sm text-white/60">Daily Activity Reporting System</p>
                    <span
                        class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></span>
                </div>

                <div class="px-6 py-6 sm:px-8">
                    {{ $slot }}
                </div>
            </div>

            <p class="tamu-naik mt-6 text-center text-xs text-white/40" style="--d:.25s">
                Badan Pusat Statistik Kota Ambon &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    @if (session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
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
                        text: @json(session('error')),
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'swal2-popup-bootstrap'
                        }
                    });
                @endif
            });
        </script>
    @endif
</body>

</html>
