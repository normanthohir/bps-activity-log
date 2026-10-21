<?php

namespace App\Support;

use App\Models\User;

/**
 * Satu definisi menu untuk desktop dan mobile.
 * Otorisasi yang sesungguhnya tetap berada di middleware/controller.
 */
class MenuNavigasi
{
    /** @return array<int,array{key:string,label:string,active:array<int,string>,items:array<int,array{label:string,route:string}>}> */
    public static function kelompok(User $user): array
    {
        $anggota = ModeKerja::anggotaAktif($user);
        $ketua = ModeKerja::ketuaAktif($user);
        $admin = $user->isAdmin();
        $kepala = $user->isKepalaBps();

        $pekerjaan = [
            self::tautan('Daftar Pekerjaan IPO', 'pekerjaan.index'),
        ];
        if (AksesPekerjaan::bolehDisposisi($user)) {
            $pekerjaan[] = self::tautan('Beri Tugas Baru', 'pekerjaan.create');
        }
        if ($anggota) {
            $pekerjaan[] = self::tautan('Tugas Saya', 'staf.tugas.index');
        }
        if ($ketua) {
            $pekerjaan[] = self::tautan('Tugas Aktif Saya', 'kabag.tugas-aktif.index');
            $pekerjaan[] = self::tautan('Tugas Tim', 'kabag.tugas.index');
        }
        if ($kepala) {
            $pekerjaan[] = self::tautan('Penugasan Kepala BPS', 'kepala-bps.tugas.index');
        }

        $standar = [self::tautan('Daftar SOP', 'sop.index')];
        if ($admin || $ketua) {
            $standar[] = self::tautan('Tambah SOP', 'sop.create');
            $standar[] = self::tautan('Kamus Kerja', 'kamus.index');
        }

        $kelompok = [
            self::bagian('pekerjaan', 'Pekerjaan', ['pekerjaan', 'staf.tugas', 'kabag.tugas', 'kabag.tugas-aktif', 'kepala-bps.tugas'], $pekerjaan),
            self::bagian('standar', 'SOP & Kamus', ['sop', 'kamus'], $standar),
        ];

        $monitoring = [];
        if ($anggota) {
            $monitoring[] = self::tautan('Laporan Harian', 'laporan.index');
        }
        if ($ketua) {
            $monitoring[] = self::tautan('Laporan Tim', 'kabag.laporan.index');
            $monitoring[] = self::tautan('Persetujuan Laporan', 'kabag.approval.index');
        }
        if ($kepala) {
            $monitoring[] = self::tautan('Rekap Kantor', 'kepala-bps.rekap');
            $monitoring[] = self::tautan('Persetujuan Laporan', 'kepala-bps.approval.index');
        }
        if ($monitoring) {
            $kelompok[] = self::bagian('monitoring', 'Monitoring',
                ['laporan', 'kabag.laporan', 'kabag.approval', 'kepala-bps.rekap', 'kepala-bps.approval'], $monitoring);
        }

        if ($admin) {
            $kelompok[] = self::bagian('administrasi', 'Administrasi', ['admin'], [
                self::tautan('Kelola Akun Pegawai', 'admin.users.index'),
                self::tautan('Kelola Tim', 'admin.bagian.index'),
            ]);
        }

        return $kelompok;
    }

    public static function dasbor(User $user): string
    {
        if ($user->isKepalaBps()) return 'kepala-bps.dashboard';
        return ModeKerja::ketuaAktif($user) ? 'kabag.dashboard' : 'dashboard';
    }

    public static function cocok(string $namaRoute, array $awalan): bool
    {
        foreach ($awalan as $item) {
            if ($namaRoute === $item || str_starts_with($namaRoute, $item.'.')) return true;
        }
        return false;
    }

    /** @return array{label:string,route:string} */
    private static function tautan(string $label, string $route): array
    {
        return compact('label', 'route');
    }

    /** @return array{key:string,label:string,active:array<int,string>,items:array<int,array{label:string,route:string}>} */
    private static function bagian(string $key, string $label, array $active, array $items): array
    {
        return compact('key', 'label', 'active', 'items');
    }
}
