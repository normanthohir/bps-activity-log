<?php

namespace App\Services;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RekapPekerjaanService
{
    /** Bobot skor (total 100). Ubah di sini kalau kantor punya aturan penilaian sendiri. */
    public const BOBOT = [
        'penyelesaian' => 50, // tugas selesai dari yang sudah seharusnya selesai
        'ketepatan' => 30,    // tugas selesai yang tepat waktu
        'pelaporan' => 20,    // tugas selesai yang sudah dilaporkan di laporan harian
    ];

    public const LABEL = [
        'sangat_baik' => 'Sangat Baik',
        'baik' => 'Baik',
        'cukup' => 'Cukup',
        'kurang' => 'Perlu Perhatian',
        'kosong' => 'Belum Dinilai',
    ];

    /**
     * Agregat per kelompok. Dua tanda tanya di dalamnya diisi tanggal hari ini.
     * "Jatuh tempo" = tugas yang sudah selesai, atau yang tenggatnya sudah lewat.
     */
    private const AGREGAT = "
        COUNT(*) AS total,
        SUM(CASE WHEN tugas.status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN tugas.status = 'dikerjakan' THEN 1 ELSE 0 END) AS dikerjakan,
        SUM(CASE WHEN tugas.status = 'belum_dikerjakan' THEN 1 ELSE 0 END) AS belum,
        SUM(CASE WHEN tugas.status <> 'selesai' AND tugas.tenggat IS NOT NULL AND tugas.tenggat < ? THEN 1 ELSE 0 END) AS terlambat,
        SUM(CASE WHEN tugas.status = 'selesai' OR (tugas.tenggat IS NOT NULL AND tugas.tenggat < ?) THEN 1 ELSE 0 END) AS jatuh_tempo,
        SUM(CASE WHEN tugas.status = 'selesai' AND (tugas.tenggat IS NULL OR DATE(tugas.updated_at) <= tugas.tenggat) THEN 1 ELSE 0 END) AS tepat,
        SUM(CASE WHEN tugas.status = 'selesai' AND EXISTS (
            SELECT 1 FROM laporan_harian lh WHERE lh.tugas_id = tugas.id AND lh.user_id = tugas.ditugaskan_ke
        ) THEN 1 ELSE 0 END) AS terlapor
    ";

    public function hitung(int $bulan, int $tahun, ?int $bagianId = null): array
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth();
        $hariIni = today()->toDateString();

        $statPegawai = $this->agregat($awal, $akhir, $bagianId, 'ditugaskan_ke', $hariIni);
        $statBagian = $this->agregat($awal, $akhir, $bagianId, 'bagian_id', $hariIni);

        $pegawai = User::with('bagian:id,nama_bagian')
            ->whereIn('role', ['staf', 'kepala_bagian'])
            ->when($bagianId, function ($q) use ($bagianId, $statPegawai) {
                $q->where(function ($w) use ($bagianId, $statPegawai) {
                    $w->where('bagian_id', $bagianId)
                        ->orWhereHas('tim', fn ($t) => $t->where('bagian.id', $bagianId))
                        ->orWhereIn('id', $statPegawai->keys()->all());
                });
            })
            ->get(['id', 'name', 'nip', 'role', 'bagian_id'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'nama' => $u->name,
                'nip' => $u->nip,
                'peran' => $u->role === 'kepala_bagian' ? 'Kepala Tim' : 'Staf',
                'bagian' => $u->bagian?->nama_bagian ?? '-',
            ] + $this->nilai($statPegawai->get($u->id)))
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

        // Skor bagian dihitung dari seluruh tugas milik bagian itu (tugas.bagian_id)
        $bagian = Bagian::with('kepalaBagian:id,name')->withCount('anggotaTim')
            ->when($bagianId, fn ($q) => $q->where('id', $bagianId))
            ->get()
            ->map(fn (Bagian $b) => [
                'id' => $b->id,
                'nama' => $b->nama_bagian,
                'kode' => $b->kode_bagian,
                'kepala' => $b->kepalaBagian?->name ?? '-',
                'anggota' => (int) $b->anggota_tim_count,
            ] + $this->nilai($statBagian->get($b->id)))
            ->sort(fn ($a, $b) => [$b['skor'] ?? -1, $a['nama']] <=> [$a['skor'] ?? -1, $b['nama']])
            ->values();

        $jumlah = fn (string $kolom) => (int) $pegawai->sum($kolom);
        $selesai = $jumlah('selesai');
        $berskor = $pegawai->filter(fn ($p) => $p['skor'] !== null);

        // Pekerjaan yang sedang terlambat (model asli, supaya tautan ke halaman detail benar)
        $terlambat = $this->periode(Tugas::query(), $awal, $akhir, $bagianId)
            ->where('tugas.status', '<>', 'selesai')
            ->whereNotNull('tugas.tenggat')
            ->where('tugas.tenggat', '<', $hariIni)
            ->with(['penerimaTugas:id,name', 'bagian:id,nama_bagian'])
            ->orderBy('tugas.tenggat')
            ->limit(5)
            ->get();

        return [
            'periode' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'label' => $awal->translatedFormat('F Y'),
            ],
            'ringkasan' => [
                'jumlah_pegawai' => $pegawai->count(),
                'punya_tugas' => $pegawai->where('total', '>', 0)->count(),
                'tanpa_tugas' => $pegawai->where('total', 0)->count(),
                'total_tugas' => $jumlah('total'),
                'selesai' => $selesai,
                'dikerjakan' => $jumlah('dikerjakan'),
                'belum' => $jumlah('belum'),
                'terlambat' => $jumlah('terlambat'),
                'tepat_waktu' => $selesai > 0 ? round($jumlah('tepat') / $selesai * 100) : null,
                'rata_skor' => $berskor->isNotEmpty() ? round($berskor->avg('skor'), 1) : null,
                'terbaik' => $berskor->take(3)->values(),
                'pembinaan' => $berskor->filter(fn ($p) => $p['skor'] < 70)->sortBy('skor')->take(3)->values(),
            ],
            'pegawai' => $pegawai,
            'bagian' => $bagian,
            'terlambat' => $terlambat,
        ];
    }

    /** Tugas yang "jatuh" pada bulan ini: menurut tenggat, atau tanggal mulai, atau tanggal dibuat kalau tenggat kosong. */
    private function periode($query, Carbon $awal, Carbon $akhir, ?int $bagianId)
    {
        return $query
            ->whereRaw(
                'COALESCE(tugas.tenggat, tugas.tanggal_mulai, DATE(tugas.created_at)) BETWEEN ? AND ?',
                [$awal->toDateString(), $akhir->toDateString()]
            )
            ->when($bagianId, fn ($q) => $q->where('tugas.bagian_id', $bagianId));
    }

    private function agregat(Carbon $awal, Carbon $akhir, ?int $bagianId, string $kolom, string $hariIni): Collection
    {
        return $this->periode(DB::table('tugas'), $awal, $akhir, $bagianId)
            ->selectRaw("tugas.{$kolom} AS kunci, " . self::AGREGAT, [$hariIni, $hariIni])
            ->groupBy("tugas.{$kolom}")
            ->get()
            ->keyBy('kunci');
    }

    private function nilai(?object $s): array
    {
        $total = (int) ($s->total ?? 0);
        $selesai = (int) ($s->selesai ?? 0);
        $jatuhTempo = (int) ($s->jatuh_tempo ?? 0);
        $tepat = (int) ($s->tepat ?? 0);
        $terlapor = (int) ($s->terlapor ?? 0);

        $penyelesaian = $jatuhTempo > 0 ? round($selesai / $jatuhTempo * 100, 1) : null;
        $ketepatan = $selesai > 0 ? round($tepat / $selesai * 100, 1) : null;
        $pelaporan = $selesai > 0 ? round($terlapor / $selesai * 100, 1) : null;
        $skor = $this->skor(compact('penyelesaian', 'ketepatan', 'pelaporan'));

        return [
            'total' => $total,
            'selesai' => $selesai,
            'dikerjakan' => (int) ($s->dikerjakan ?? 0),
            'belum' => (int) ($s->belum ?? 0),
            'terlambat' => (int) ($s->terlambat ?? 0),
            'jatuh_tempo' => $jatuhTempo,
            'tepat' => $tepat,
            'terlapor' => $terlapor,
            'penyelesaian' => $penyelesaian,
            'ketepatan' => $ketepatan,
            'pelaporan' => $pelaporan,
            'skor' => $skor,
            'tingkat' => RekapKinerjaService::tingkat($skor, $total),
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
}