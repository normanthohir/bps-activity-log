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
            self::tautan('Daftar Pekerjaan', 'pekerjaan.index'),
        ];
        if (AksesPekerjaan::bolehDisposisi($user)) {
            $pekerjaan[] = self::tautan('Pekerjaan / disposisi baru', 'pekerjaan.create');
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
            $monitoring[] = self::tautan('Laporan Harian', 'laporan.index', 'laporan');
        }
        if ($ketua) {
            $monitoring[] = self::tautan('Persetujuan Laporan Tim', 'kabag.approval.index', 'persetujuan');
        }
        if ($kepala) {
            $monitoring[] = self::tautan('Persetujuan Laporan', 'kepala-bps.approval.index', 'persetujuan');
            $monitoring[] = self::tautan('Rekap Kantor', 'kepala-bps.rekap', 'monitoring');
        }
        if ($monitoring) {
            $kelompok[] = self::bagian(
                'monitoring',
                'Monitoring',
                ['laporan', 'kabag.approval', 'kepala-bps.rekap', 'kepala-bps.approval'],
                $monitoring
            );
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
            if ($namaRoute === $item || str_starts_with($namaRoute, $item . '.')) return true;
        }
        return false;
    }

    /** @return array{label:string,route:string} */
    /** Item: label, route, dan ikon opsional (kunci ikon di navigation.blade.php). */
    private static function tautan(string $label, string $route, ?string $ikon = null): array
    {
        return array_filter(compact('label', 'route', 'ikon'), fn($v) => $v !== null);
    }

    /** @return array{key:string,label:string,active:array<int,string>,items:array<int,array{label:string,route:string}>} */
    private static function bagian(string $key, string $label, array $active, array $items): array
    {
        return compact('key', 'label', 'active', 'items');
    }
}
