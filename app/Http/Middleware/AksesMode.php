<?php

namespace App\Http\Middleware;

use App\Support\ModeKerja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AksesMode
{
    /** Pemakaian di route: ->middleware('mode:anggota') atau ->middleware('mode:ketua') */
    public function handle(Request $request, Closure $next, string $mode): Response
    {
        $user = $request->user();

        $boleh = $user && match ($mode) {
            'anggota' => ModeKerja::anggotaAktif($user),
            'ketua' => ModeKerja::ketuaAktif($user),
            default => false,
        };

        abort_unless($boleh, 403, 'Halaman ini tidak tersedia pada mode kerja Anda saat ini.');

        return $next($request);
    }
}