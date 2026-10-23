<?php

namespace App\Services;

use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RekapKinerjaService
{
    /**
     * Bobot skor (total 100). Ubah di sini kalau kantor punya aturan penilaian sendiri.
     */
    public const BOBOT = [
        'disiplin' => 40, // seberapa sering melapor pada hari kerja
        'kualitas' => 35, // laporan disetujui dibanding dikembalikan
        'hasil' => 25,    // laporan dengan output "selesai"
    ];

    public const LABEL = [
        'sangat_baik' => 'Sangat Baik',
        'baik' => 'Baik',
        'cukup' => 'Cukup',
        'kurang' => 'Perlu Perhatian',
        'kosong' => 'Belum Melapor',
    ];

    public function hitung(int $bulan, int $tahun, ?int $bagianId = null): array
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();

        // Hari kerja dihitung sampai hari ini saja kalau bulannya belum berakhir
        $batas = $akhir->isFuture() ? now()->startOfDay() : $akhir;
        $hariKerja = $batas->lt($awal)
            ? 0
            : collect(CarbonPeriod::create($awal, $batas))->reject(fn ($h) => $h->isWeekend())->count();

        // Satu query untuk semua pegawai (draft tidak dihitung)
        $statistik = LaporanHarian::query()
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->where('status', '!=', 'draft')
            ->selectRaw("user_id,
                COUNT(*) AS total,
                COUNT(DISTINCT tanggal) AS hari_lapor,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) AS disetujui,
                SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) AS dikembalikan,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
                SUM(CASE WHEN output = 'selesai' THEN 1 ELSE 0 END) AS selesai,
                SUM(CASE WHEN output = 'tertunda' THEN 1 ELSE 0 END) AS tertunda,
                SUM(CASE WHEN output = 'gagal' THEN 1 ELSE 0 END) AS gagal")
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $pegawai = User::with('bagian:id,nama_bagian')
            ->whereIn('role', ['staf', 'kepala_bagian'])
            ->when($bagianId, fn ($q) => $q->where('bagian_id', $bagianId))
            ->get(['id', 'name', 'nip', 'role', 'bagian_id'])
            ->map(function (User $u) use ($statistik, $hariKerja) {
                $s = $statistik->get($u->id);

                $total = (int) ($s->total ?? 0);
                $hariLapor = (int) ($s->hari_lapor ?? 0);
                $disetujui = (int) ($s->disetujui ?? 0);
                $dikembalikan = (int) ($s->dikembalikan ?? 0);
                $selesai = (int) ($s->selesai ?? 0);
                $diproses = $disetujui + $dikembalikan;

                $disiplin = $hariKerja > 0 ? min(100, round($hariLapor / $hariKerja * 100, 1)) : null;
                $kualitas = $diproses > 0 ? round($disetujui / $diproses * 100, 1) : null;
                $hasil = $total > 0 ? round($selesai / $total * 100, 1) : null;
                $skor = $this->skor(compact('disiplin', 'kualitas', 'hasil'));

                return [
                    'id' => $u->id,
                    'nama' => $u->name,
                    'nip' => $u->nip,
                    'peran' => $u->role === 'kepala_bagian' ? 'Kepala Tim' : 'Staf',
                    'bagian_id' => $u->bagian_id,
                    'bagian' => $u->bagian?->nama_bagian ?? '-',
                    'hari_lapor' => $hariLapor,
                    'total' => $total,
                    'disetujui' => $disetujui,
                    'dikembalikan' => $dikembalikan,
                    'menunggu' => (int) ($s->menunggu ?? 0),
                    'selesai' => $selesai,
                    'tertunda' => (int) ($s->tertunda ?? 0),
                    'gagal' => (int) ($s->gagal ?? 0),
                    'disiplin' => $disiplin,
                    'kualitas' => $kualitas,
                    'hasil' => $hasil,
                    'skor' => $skor,
                    'tingkat' => self::tingkat($skor, $total),
                ];
            })
            ->sort(fn ($a, $b) => [$b['skor'] ?? -1, $a['nama']] <=> [$a['skor'] ?? -1, $b['nama']])
            ->values();

        // Peringkat (skor yang sama mendapat peringkat yang sama)
        $peringkat = 0;
        $sebelumnya = null;
        $pegawai = $pegawai->map(function ($p, $i) use (&$peringkat, &$sebelumnya) {
            if ($p['skor'] !== $sebelumnya) {
                $peringkat = $i + 1;
                $sebelumnya = $p['skor'];
            }
            $p['peringkat'] = $peringkat;

            return $p;
        });

        $bagian = Bagian::with('kepalaBagian:id,name')
            ->when($bagianId, fn ($q) => $q->where('id', $bagianId))
            ->get(['id', 'nama_bagian', 'kode_bagian', 'kepala_bagian_id'])
            ->map(function (Bagian $b) use ($pegawai) {
                $anggota = $pegawai->where('bagian_id', $b->id);
                $nilai = $anggota->pluck('skor')->filter(fn ($s) => $s !== null);
                $skor = $nilai->isNotEmpty() ? round($nilai->avg(), 1) : null;
                $total = (int) $anggota->sum('total');

                return [
                    'id' => $b->id,
                    'nama' => $b->nama_bagian,
                    'kode' => $b->kode_bagian,
                    'kepala' => $b->kepalaBagian?->name ?? '-',
                    'jumlah_pegawai' => $anggota->count(),
                    'melapor' => $anggota->where('total', '>', 0)->count(),
                    'belum_lapor' => $anggota->where('total', 0)->count(),
                    'total' => $total,
                    'disetujui' => (int) $anggota->sum('disetujui'),
                    'dikembalikan' => (int) $anggota->sum('dikembalikan'),
                    'skor' => $skor,
                    'tingkat' => self::tingkat($skor, $anggota->count() > 0 ? max($total, 1) : 0),
                ];
            })
            ->sort(fn ($a, $b) => [$b['skor'] ?? -1, $a['nama']] <=> [$a['skor'] ?? -1, $b['nama']])
            ->values();

        $berskor = $pegawai->filter(fn ($p) => $p['skor'] !== null);
        $disetujui = (int) $pegawai->sum('disetujui');
        $dikembalikan = (int) $pegawai->sum('dikembalikan');

        return [
            'periode' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'label' => $awal->translatedFormat('F Y'),
                'hari_kerja' => $hariKerja,
            ],
            'ringkasan' => [
                'jumlah_pegawai' => $pegawai->count(),
                'melapor' => $pegawai->where('total', '>', 0)->count(),
                'belum_lapor' => $pegawai->where('total', 0)->count(),
                'total_laporan' => (int) $pegawai->sum('total'),
                'tingkat_persetujuan' => ($disetujui + $dikembalikan) > 0
                    ? round($disetujui / ($disetujui + $dikembalikan) * 100)
                    : null,
                'rata_skor' => $berskor->isNotEmpty() ? round($berskor->avg('skor'), 1) : null,
                'terbaik' => $pegawai->where('total', '>', 0)->take(3)->values(),
                'pembinaan' => $berskor->filter(fn ($p) => $p['skor'] < 70)->sortBy('skor')->take(3)->values(),
            ],
            'pegawai' => $pegawai,
            'bagian' => $bagian,
        ];
    }

    /** Rata-rata tertimbang. Komponen yang belum ada datanya dilewati dan bobotnya dibagi ulang. */
    private function skor(array $komponen): ?float
    {
        $nilai = 0;
        $bobot = 0;

        foreach ($komponen as $nama => $v) {
            if ($v === null) {
                continue;
            }
            $nilai += $v * self::BOBOT[$nama];
            $bobot += self::BOBOT[$nama];
        }

        return $bobot > 0 ? round($nilai / $bobot, 1) : null;
    }

    public static function tingkat(?float $skor, int $total): string
    {
        return match (true) {
            $skor === null || $total === 0 => 'kosong',
            $skor >= 85 => 'sangat_baik',
            $skor >= 70 => 'baik',
            $skor >= 55 => 'cukup',
            default => 'kurang',
        };
    }
}