<?php

namespace App\Support;

use App\Models\User;

/** Mode UI pegawai. Hak ketua selalu diverifikasi pada tabel keanggotaan tim. */
class ModeKerja
{
    public const ANGGOTA = 'anggota';
    public const KETUA = 'ketua';

    public static function ketuaAktif(User $user): bool
    {
        // Kepala BPS dan Admin tetap memiliki hak sistem tersendiri.
        if ($user->isAdmin() || $user->isKepalaBps() || !$user->isKepalaBagian()) {
            return false;
        }

        // Jangan pernah memakai input request/cookie sebagai penentu hak akses.
        return request()->hasSession() && request()->session()->get('mode_kerja') === self::KETUA;
    }

    public static function anggotaAktif(User $user): bool
    {
        return $user->isStaf() && !self::ketuaAktif($user);
    }

    public static function label(User $user): string
    {
        if ($user->isAdmin()) return 'Admin';
        if ($user->isKepalaBps()) return 'Kepala BPS';
        return self::ketuaAktif($user) ? 'Ketua Tim' : 'Anggota Tim';
    }

    public static function timDipimpinAktifIds(User $user): array
    {
        return self::ketuaAktif($user) ? $user->timDipimpinIds() : [];
    }
}
