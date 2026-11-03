<?php

namespace App\Support;

use App\Models\LaporanHarian;
use App\Models\User;
use Illuminate\Support\Collection;

class PengingatLaporan
{
    /**
     * Pekerjaan milik pengguna yang sudah selesai tapi belum punya laporan.
     * Di mode Ketua Tim hasilnya selalu kosong.
     */
    public static function untuk(User $user): Collection
    {
        if (ModeKerja::ketuaAktif($user)) {
            return collect();
        }

        return $user->tugasDiterima()
            ->where('status', 'selesai')
            ->whereNotIn('id', LaporanHarian::where('user_id', $user->id)
                ->whereNotNull('tugas_id')
                ->select('tugas_id'))
            ->latest('updated_at')
            ->get(['id', 'judul', 'tenggat', 'updated_at']);
    }
    public static function terlambat(User $user): Collection
    {
        if (ModeKerja::ketuaAktif($user)) {
            return collect();
        }

        return $user->tugasDiterima()
            ->where('status', '!=', 'selesai')
            ->whereNotNull('tenggat')
            ->whereDate('tenggat', '<', today())
            ->orderBy('tenggat')
            ->get(['id', 'judul', 'tenggat', 'status']);
    }
}
