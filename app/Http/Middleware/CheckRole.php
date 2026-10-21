<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Contoh pakai di route: ->middleware('role:kepala_bagian,kepala_bps')
     * Boleh lebih dari satu role dipisah koma.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! collect($roles)->contains(fn ($role) => match ($role) {
            'kepala_bagian' => \App\Support\ModeKerja::ketuaAktif($user),
            'staf' => \App\Support\ModeKerja::anggotaAktif($user),
            default => $user->role === $role,
        })) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
