<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\ModeKerja;
use App\Support\NotifLonceng;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiApiController extends Controller
{
    /** GET /api/notifikasi : data lonceng sesuai peran/mode (null = tanpa lonceng). */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // $notifAnggota hanya dibutuhkan peran anggota / kepala tim (mode anggota)
        $anggota = null;
        if (! $user->isAdmin() && ! $user->isKepalaBps() && ! ModeKerja::ketuaAktif($user)) {
            $anggota = NotifLonceng::untuk($user); // ← lihat langkah 3
        }

        $notif = NotifLonceng::untuk($user, $anggota);

        if ($notif) {
            $notif['groups'] = collect($notif['groups'])->map(function ($g) {
                $g['items'] = collect($g['items'])->map(function ($i) {
                    $i['path'] = self::path($i['url'] ?? null);
                    unset($i['url']); // URL web tidak berguna di mobile
                    return $i;
                })->all();
                return $g;
            })->all();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil diambil.',
            'data' => $notif,
        ]);
    }

    private static function path(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $p = parse_url($url);

        return ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
    }
}