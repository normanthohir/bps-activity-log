<?php

namespace App\Support;

use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Menyusun data lonceng (untuk <x-notif-bell>) sesuai peran.
 * Komponen hanya menampilkan; isi tiap peran diatur di sini.
 */
class NotifLonceng
{
    public static function untuk($user, ?array $anggota = null): ?array
    {
        return match (true) {
            $user->isAdmin() => self::admin(),
            $user->isKepalaBps() => self::kepalaBps(),
            $user->role === 'kepala_bagian' && ModeKerja::ketuaAktif($user) => self::ketuaUntuk($user),
            in_array($user->role, ['staf', 'kepala_bagian'], true)
            => $anggota ? self::anggota($anggota) : self::anggotaUntuk($user),
            default => null,
        };
    }

    /** Anggota & Kepala Tim (mode anggota). $n = array $notifAnggota yang sudah ada. */
    public static function anggota(array $n): array
    {
        return [
            'total' => $n['total'],
            'mendesak' => $n['mendesak'],
            'kosong' => 'Tidak ada laporan dikembalikan atau pekerjaan tertunda.',
            'groups' => [
                [
                    'tone' => 'red',
                    'judul' => $n['dikembalikan']->count() . ' laporan dikembalikan',
                    'items' => $n['dikembalikan']->map(fn($l) => [
                        'judul' => $l->tanggal->format('d M Y') . ' · ' . Str::limit($l->uraian, 45),
                        'catatan' => 'Catatan: ' . ($l->logApproval->last()?->catatan ?? '-'),
                        'url' => route('laporan.edit', $l),
                        'aksi' => 'Revisi',
                    ])->all(),
                ],
                [
                    'tone' => 'orange',
                    'judul' => $n['terlambat']->count() . ' pekerjaan lewat tenggat',
                    'items' => $n['terlambat']->map(fn($t) => [
                        'judul' => $t->judul,
                        'sub' => 'Tenggat ' . $t->tenggat->format('d M Y') . ' · '
                            . ($t->status === 'dikerjakan' ? 'Sedang dikerjakan' : 'Belum dikerjakan'),
                        'badge' => (int) abs($t->tenggat->copy()->startOfDay()->diffInDays(today())) . ' hari',
                        'url' => route('pekerjaan.show', $t),
                    ])->all(),
                ],
                [
                    'tone' => 'green',
                    'judul' => $n['belum']->count() . ' pekerjaan selesai, belum dilaporkan',
                    'items' => $n['belum']->map(fn($t) => [
                        'judul' => $t->judul,
                        'sub' => 'Selesai ' . $t->updated_at->diffForHumans(),
                        'url' => route('laporan.create', ['tugas' => $t->id]),
                        'aksi' => 'Buat laporan',
                    ])->all(),
                ],
            ],
        ];
    }

    /**
     * Kepala Tim (mode ketua): hitung sendiri, memakai query yang sama
     * dengan DashboardController@kabag, sehingga tidak bergantung pada
     * variabel dari controller (yang tidak sampai ke layout).
     */
    public static function ketuaUntuk($user): array
    {
        $ids = ModeKerja::timDipimpinAktifIds($user);

        $menunggu = LingkupLaporanTim::untukKetua($user)->menunggu()->count();

        $terlambat = Tugas::whereIn('bagian_id', $ids)
            ->where('status', '!=', 'selesai')
            ->whereNotNull('tenggat')
            ->where('tenggat', '<', today()->toDateString())
            ->count();

        return self::ketua($menunggu, $terlambat);
    }

    public static function ketua(int $menungguApproval, int $tugasTerlambat): array
    {
        $groups = [];

        if ($menungguApproval > 0) {
            $groups[] = [
                'tone' => 'amber',
                'judul' => 'Perlu persetujuan',
                'items' => [[
                    'judul' => "{$menungguApproval} laporan tim menunggu persetujuan",
                    'url' => route('kabag.approval.index', ['status' => 'menunggu']),
                    'aksi' => 'Proses sekarang',
                ]],
            ];
        }

        if ($tugasTerlambat > 0) {
            $groups[] = [
                'tone' => 'orange',
                'judul' => 'Pekerjaan tim terlambat',
                'items' => [[
                    'judul' => "{$tugasTerlambat} pekerjaan tim sudah melewati tenggat",
                    'url' => route('pekerjaan.index', ['terlambat' => 1]),
                    'aksi' => 'Lihat pekerjaan',
                ]],
            ];
        }

        return [
            'judul' => 'Perlu tindakan Anda',
            'total' => $menungguApproval + $tugasTerlambat,
            'mendesak' => $tugasTerlambat,
            'kosong' => 'Tidak ada laporan menunggu atau pekerjaan tim yang terlambat.',
            'groups' => $groups,
        ];
    }

    /**
     * Kepala BPS Kota: sama seperti Kepala Tim, tetapi mencakup SELURUH tim
     * (kepala tim sampai anggota), tanpa dibatasi tim yang dipimpin.
     */
    public static function kepalaBps(): array
    {
        $hariIni = today()->toDateString();
        $groups = [];

        // 1. Laporan menunggu persetujuan (seluruh organisasi)
        $menunggu = LaporanHarian::menunggu()->count();
        if ($menunggu > 0) {
            // Sesuaikan dengan nama route halaman approval milik Kepala BPS
            $routeApproval = collect(['bps.approval.index', 'kepala-bps.approval.index', 'kabag.approval.index'])
                ->first(fn($r) => Route::has($r));

            $groups[] = [
                'tone' => 'amber',
                'judul' => 'Perlu persetujuan',
                'items' => [[
                    'judul' => "{$menunggu} laporan menunggu persetujuan",
                    'sub' => 'Seluruh tim',
                    'url' => $routeApproval ? route($routeApproval, ['status' => 'menunggu']) : route('dashboard'),
                    'aksi' => 'Proses sekarang',
                ]],
            ];
        }

        // 2. Pekerjaan terlambat, dirinci per tim
        $terlambatPerTim = Tugas::where('status', '!=', 'selesai')
            ->whereNotNull('tenggat')
            ->where('tenggat', '<', $hariIni)
            ->selectRaw('bagian_id, count(*) as jumlah')
            ->groupBy('bagian_id')
            ->orderByDesc('jumlah')
            ->pluck('jumlah', 'bagian_id');

        $totalTerlambat = (int) $terlambatPerTim->sum();
        if ($totalTerlambat > 0) {
            $namaTim = Bagian::whereIn('id', $terlambatPerTim->keys())->pluck('nama_bagian', 'id');

            $groups[] = [
                'tone' => 'orange',
                'judul' => "{$totalTerlambat} pekerjaan lewat tenggat",
                'items' => $terlambatPerTim->take(8)->map(fn($jumlah, $bagianId) => [
                    'judul' => $namaTim[$bagianId] ?? 'Tanpa tim',
                    'sub' => 'Belum selesai dan melewati tenggat',
                    'badge' => "{$jumlah} pekerjaan",
                    'url' => route('pekerjaan.index', ['terlambat' => 1]),
                ])->values()->all(),
            ];
        }

        return [
            'judul' => 'Ringkasan seluruh tim',
            'total' => $menunggu + $totalTerlambat,
            'mendesak' => $totalTerlambat,
            'kosong' => 'Tidak ada laporan menunggu atau pekerjaan terlambat di seluruh tim.',
            'groups' => $groups,
        ];
    }

    /** Anggota & Kepala Tim (mode anggota): hitung sendiri, tanpa view composer. */
public static function anggotaUntuk($user): array
{
    $dikembalikan = $user->laporanHarian()
        ->where('status', 'dikembalikan')
        ->with('logApproval')
        ->latest('tanggal')
        ->get();

    $terlambat = collect(PengingatLaporan::terlambat($user));
    $belum = collect(PengingatLaporan::untuk($user));

    return self::anggota([
        'total' => $dikembalikan->count() + $terlambat->count() + $belum->count(),
        'mendesak' => $dikembalikan->count() + $terlambat->count(),
        'dikembalikan' => $dikembalikan,
        'terlambat' => $terlambat,
        'belum' => $belum,
    ]);
}

    /** Admin — kerangka, isi sesuai kebutuhan. */
    public static function admin(): ?array
    {
        // Contoh: akun baru menunggu aktivasi, error sistem, dll.
        return null;
    }
}
