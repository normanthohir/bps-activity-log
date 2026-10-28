<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TugasLaporanSeeder extends Seeder
{
    private const ALASAN_GAGAL = [
        'Responden menolak diwawancara setelah tiga kali kunjungan.',
        'Data sumber dari instansi terkait tidak lengkap.',
        'Aplikasi pengolahan error dan data tidak tersimpan.',
        'Lokasi pendataan tidak dapat dijangkau karena cuaca buruk.',
    ];

    private const ALASAN_TERTUNDA = [
        'Menunggu data dari dinas terkait.',
        'Kegiatan diundur karena ada rapat koordinasi mendadak.',
        'Menunggu persetujuan revisi kuesioner dari pusat.',
        'Jaringan internet kantor sedang terganggu.',
    ];

    private const CATATAN_DISETUJUI = [
        null, null, 'Sudah sesuai.', 'Bagus, pertahankan.', 'Laporan lengkap dan jelas.',
    ];

    private const CATATAN_DITOLAK = [
        'Uraian kegiatan terlalu singkat, mohon dilengkapi.',
        'Jam kegiatan tidak sesuai, mohon diperiksa kembali.',
        'Mohon cantumkan hasil kegiatan yang lebih rinci.',
        'Lampiran bukti belum ada, mohon dilengkapi.',
    ];

    public function run(): void
    {
        $kepalaBps = User::where('role', 'kepala_bps')->firstOrFail();

        // Kamus kerja per bagian jadi sumber judul tugas dan uraian laporan
        $kamus = DB::table('kamus_kerja')->whereNotNull('bagian_id')->get()->groupBy('bagian_id');

        foreach (DB::table('bagian')->orderBy('id')->get() as $bagian) {
            $daftar = $kamus->get($bagian->id);
            $pegawai = User::where('bagian_id', $bagian->id)->get();
            $kepalaTim = $pegawai->firstWhere('role', 'kepala_tim');

            if (! $daftar || ! $kepalaTim) {
                continue;
            }

            // Laporan harian mandiri semua pegawai (termasuk kepala tim)
            foreach ($pegawai as $p) {
                $this->laporanHarian($p, $daftar, $kepalaBps);
            }

            // Kepala tim memberi 2 tugas ke tiap staf
            foreach ($pegawai->where('role', 'staf') as $staf) {
                for ($i = 0; $i < 2; $i++) {
                    $this->buatTugas($kepalaTim, $staf, $daftar, $kepalaBps);
                }
            }

            // Kepala BPS memberi 1 tugas ke tiap kepala tim
            $this->buatTugas($kepalaBps, $kepalaTim, $daftar, $kepalaBps);
        }

        // Kepala BPS juga memberi tugas langsung ke beberapa staf acak
        foreach (User::where('role', 'staf')->inRandomOrder()->limit(6)->get() as $staf) {
            if ($daftar = $kamus->get($staf->bagian_id)) {
                $this->buatTugas($kepalaBps, $staf, $daftar, $kepalaBps);
            }
        }
    }

    // Laporan harian mandiri 30 hari terakhir (hanya hari kerja)
    private function laporanHarian(User $pegawai, $daftarKamus, User $kepalaBps): void
    {
        for ($i = 30; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->startOfDay();

            // Lewati akhir pekan, dan sesekali pegawai tidak melapor (25%)
            if ($tanggal->isWeekend() || mt_rand(1, 100) <= 25) {
                continue;
            }

            $this->simpanLaporan($pegawai, $tanggal, $daftarKamus->random()->nama, null, $kepalaBps);
        }
    }

    private function buatTugas(User $pemberi, User $penerima, $daftarKamus, User $kepalaBps): void
    {
        $k = $daftarKamus->random();
        $kondisi = $this->acak(['belum' => 30, 'dikerjakan' => 35, 'selesai' => 35]);
        $dibuat = now()->subDays(mt_rand(10, 25));

        $tugasId = DB::table('tugas')->insertGetId([
            'dibuat_oleh' => $pemberi->id,
            'ditugaskan_ke' => $penerima->id,
            'bagian_id' => $penerima->bagian_id,
            'judul' => $k->nama,
            'deskripsi' => 'Input: ' . $k->input . '. Proses: ' . $k->proses . '.',
            'tanggal_mulai' => $dibuat->copy()->addDays(mt_rand(0, 3))->toDateString(),
            'tenggat' => $kondisi === 'selesai'
                ? now()->subDays(mt_rand(1, 8))->toDateString()
                : now()->addDays(mt_rand(3, 20))->toDateString(),
            'status' => match ($kondisi) {
                'belum' => 'belum_dikerjakan',
                'dikerjakan' => 'dikerjakan',
                'selesai' => 'selesai',
            },
            'jenis_input' => 'manual',
            'uraian_input' => $k->input,
            'output_rencana' => $k->output,
            'output_realisasi' => $kondisi === 'selesai' ? $k->output : null,
            'kamus_kerja_id' => $k->id,
            'created_at' => $dibuat,
            'updated_at' => $dibuat,
        ]);

        if ($kondisi === 'dikerjakan') {
            $this->simpanLaporan(
                $penerima, now()->subDays(mt_rand(1, 6))->startOfDay(), $k->nama, $tugasId, $kepalaBps,
                paksaStatus: $this->acak(['menunggu' => 60, 'disetujui' => 40]),
            );
        }

        if ($kondisi === 'selesai') {
            $this->simpanLaporan(
                $penerima, now()->subDays(mt_rand(8, 12))->startOfDay(), $k->nama, $tugasId, $kepalaBps,
                paksaStatus: 'disetujui',
            );
            $this->simpanLaporan(
                $penerima, now()->subDays(mt_rand(1, 7))->startOfDay(), $k->nama, $tugasId, $kepalaBps,
                paksaStatus: 'disetujui', paksaOutput: 'selesai',
            );
        }
    }

    private function simpanLaporan(
        User $pegawai,
        Carbon $tanggal,
        string $uraian,
        ?int $tugasId,
        User $kepalaBps,
        ?string $paksaStatus = null,
        ?string $paksaOutput = null,
    ): void {
        $output = $paksaOutput ?? $this->acak(['selesai' => 70, 'tertunda' => 15, 'gagal' => 15]);

        $alasan = match ($output) {
            'gagal' => self::ALASAN_GAGAL[array_rand(self::ALASAN_GAGAL)],
            'tertunda' => self::ALASAN_TERTUNDA[array_rand(self::ALASAN_TERTUNDA)],
            default => null,
        };

        // Laporan lama kebanyakan sudah diproses, laporan baru kebanyakan masih menunggu
        $umur = (int) abs($tanggal->diffInDays(now()));
        $status = $paksaStatus ?? ($umur <= 2
            ? $this->acak(['menunggu' => 50, 'draft' => 30, 'disetujui' => 20])
            : $this->acak(['disetujui' => 72, 'dikembalikan' => 13, 'menunggu' => 15]));

        $jamMulai = mt_rand(7, 10);
        $jamSelesai = $jamMulai + mt_rand(2, 6);
        $dibuat = $tanggal->copy()->setTime($jamSelesai, mt_rand(0, 59));

        $laporanId = DB::table('laporan_harian')->insertGetId([
            'user_id' => $pegawai->id,
            'tugas_id' => $tugasId,
            'tanggal' => $tanggal->toDateString(),
            'jam_mulai' => sprintf('%02d:00:00', $jamMulai),
            'jam_selesai' => sprintf('%02d:%02d:00', $jamSelesai, mt_rand(0, 59)),
            'uraian' => $uraian,
            'output' => $output,
            'alasan' => $alasan,
            'lokasi' => $this->acak(['kantor' => 65, 'lapangan' => 25, 'dinas_luar' => 10]),
            'status' => $status,
            'created_at' => $dibuat,
            'updated_at' => $dibuat,
        ]);

        if (! in_array($status, ['disetujui', 'dikembalikan'])) {
            return;
        }

        // Kepala tim tidak boleh menyetujui laporannya sendiri, jadi laporan kepala tim
        // diproses Kepala BPS. Laporan staf diproses atasannya, sesekali Kepala BPS.
        $penyetujuId = ($pegawai->role === 'kepala_tim' || mt_rand(1, 100) <= 15)
            ? $kepalaBps->id
            : ($pegawai->atasan_id ?? $kepalaBps->id);

        $waktu = $tanggal->copy()->addDay()->setTime(mt_rand(8, 16), mt_rand(0, 59));
        if ($waktu->isFuture()) {
            $waktu = now();
        }

        DB::table('laporan_harian')->where('id', $laporanId)->update([
            'disetujui_oleh' => $penyetujuId,
            'disetujui_pada' => $waktu,
        ]);

        DB::table('log_approval')->insert([
            'laporan_id' => $laporanId,
            'approver_id' => $penyetujuId,
            'aksi' => $status === 'disetujui' ? 'disetujui' : 'ditolak',
            'catatan' => $status === 'disetujui'
                ? self::CATATAN_DISETUJUI[array_rand(self::CATATAN_DISETUJUI)]
                : self::CATATAN_DITOLAK[array_rand(self::CATATAN_DITOLAK)],
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);
    }

    // Pilih satu kunci secara acak berdasarkan bobot
    private function acak(array $bobot): string
    {
        $sisa = mt_rand(1, array_sum($bobot));

        foreach ($bobot as $kunci => $nilai) {
            $sisa -= $nilai;
            if ($sisa <= 0) {
                return $kunci;
            }
        }

        return array_key_last($bobot);
    }
}