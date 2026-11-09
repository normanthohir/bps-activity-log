<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\MenuNavigasi;
use App\Support\ModeKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuApiController extends Controller
{
    /**
     * GET /api/menu
     * Mengembalikan profil, mode aktif, dan menu sidebar sesuai role + mode.
     * Memakai MenuNavigasi yang sama dengan web, jadi menu tidak perlu ditulis dua kali.
     * Field "route" dipakai Flutter sebagai kunci layar (bukan URL).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diambil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'mode' => [
                    'label' => ModeKerja::label($user),
                    'ketua_aktif' => ModeKerja::ketuaAktif($user),
                    'bisa_pindah_mode' => $user->isKepalaBagian()
                        && !$user->isAdmin() && !$user->isKepalaBps(),
                ],
                'dasbor' => MenuNavigasi::dasbor($user),
                'menu' => MenuNavigasi::kelompok($user),
            ],
        ]);
    }
}