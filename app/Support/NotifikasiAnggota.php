<?php

namespace App\Support;

use App\Models\LaporanHarian;
use App\Models\User;

class NotifikasiAnggota
{
    /**
     * Data notifikasi untuk lonceng di topbar.
     * Mengembalikan null bila pengguna bukan anggota atau sedang di mode Ketua Tim.
     */
    public static function untuk(User $user): ?array
    {
        if (!in_array($user->role, ['staf', 'kepala_bagian'], true) || !ModeKerja::anggotaAktif($user)) {
            return null;
        }

        $dikembalikan = LaporanHarian::with('logApproval')
            ->where('user_id', $user->id)
            ->where('status', 'dikembalikan')
            ->latest('tanggal')
            ->get();

        $terlambat = PengingatLaporan::terlambat($user);
        $belum = PengingatLaporan::untuk($user);

        return [
            'dikembalikan' => $dikembalikan,
            'terlambat' => $terlambat,
            'belum' => $belum,
            'mendesak' => $dikembalikan->count() + $terlambat->count(),
            'total' => $dikembalikan->count() + $terlambat->count() + $belum->count(),
        ];
    }
}