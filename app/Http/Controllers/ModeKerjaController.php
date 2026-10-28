<?php

namespace App\Http\Controllers;

use App\Support\ModeKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModeKerjaController extends Controller
{
    /** Berpindah tampilan dan hak kerja, tanpa mengganti role atau keanggotaan di database. */
    public function ganti(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isStaf() && !$user->isAdmin() && !$user->isKepalaBps(), 403);
        $data = $request->validate(['mode' => ['required', Rule::in([ModeKerja::ANGGOTA, ModeKerja::KETUA])]]);
        if ($data['mode'] === ModeKerja::KETUA) {
            // Cek DB pada setiap pergantian; pengiriman manual mode=ketua tidak memberi hak ekstra.
            abort_unless($user->isKepalaBagian(), 403, 'Anda belum terdaftar sebagai ketua tim.');
        }
        $request->session()->put('mode_kerja', $data['mode']);
        return redirect()->route('dashboard')->with('success', 'Mode kerja aktif: ' . ModeKerja::label($user) . '.');
    }
}
