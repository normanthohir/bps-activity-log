<?php

namespace App\Support;

use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Kontrak kolom Excel dibuat sama untuk unduh template dan unggah. */
class ExcelMasterData
{
    public const AKUN = ['nama', 'nip', 'email', 'role', 'kode_tim_utama', 'nip_atasan', 'kode_tim_tambahan', 'kode_tim_dipimpin'];
    public const SOP = ['kode_sop', 'nama_sop', 'versi', 'keterangan', 'urutan', 'input', 'proses', 'output', 'sumber_input', 'durasi_hari'];
    public const TIM = ['kode_tim', 'nama_tim', 'nip_ketua'];
    public const ANGGOTA = ['kode_tim', 'nip_pegawai'];
    public const KAMUS = ['nama', 'input', 'proses', 'output'];

    public static function unduh(string $jenis): StreamedResponse
    {
        $buku = new Spreadsheet();
        $buku->removeSheetByIndex(0);
        $petunjuk = [];
        if ($jenis === 'akun') {
            self::buatSheet($buku, 'Akun', self::AKUN);
            self::buatSheet($buku, 'Daftar Tim', ['kode_tim', 'nama_tim'], Bagian::orderBy('kode_bagian')->get()
                ->map(fn (Bagian $b) => [$b->kode_bagian, $b->nama_bagian])->all(), false);
            $petunjuk = [
                'Isi sheet Akun mulai baris 2. Baris header tidak boleh diubah.',
                'nama, nip, email wajib. role OPSIONAL: kosong (pegawai), staf, kepala_bps, atau admin.',
                'kode_tim_utama opsional. Lihat daftar kode pada sheet Daftar Tim.',
                'nip_atasan opsional; jika diisi harus menunjuk Kepala BPS atau pegawai yang memimpin paling sedikit satu tim.',
                'kode_tim_tambahan dan kode_tim_dipimpin opsional; keduanya boleh berisi banyak kode dipisah titik koma (;).',
                'Contoh: pegawai biasa dapat memimpin TIM-A; sekaligus anggota TIM-B dan TIM-C.',
                'Kolom role hanya untuk hak sistem khusus. Mode Ketua/Anggota dipilih setelah login dan mengikuti penugasan per tim.',
                'Semua kolom NIP harus berupa TEKS di Excel agar angka 18 digit tidak berubah.',
                'Impor hanya MENAMBAH akun baru; NIP dan email yang sudah terdaftar akan ditolak.',
                'Password awal setiap akun hasil impor sama dengan form manual: password. Segera ganti sesudah masuk.',
                'Semua baris divalidasi terlebih dahulu; jika ada satu kesalahan, tidak ada data yang disimpan.',
            ];
        } elseif ($jenis === 'sop') {
            self::buatSheet($buku, 'SOP', self::SOP);
            $petunjuk = [
                'Satu baris = satu langkah. Beberapa baris dengan kode_sop sama menjadi satu SOP.',
                'kode_sop, nama_sop, versi, urutan, proses, sumber_input, durasi_hari wajib.',
                'Ulangi nama_sop, versi, keterangan pada setiap baris untuk kode_sop yang sama.',
                'urutan dimulai dari 1 dan harus berurutan tanpa loncatan; maksimal 50 langkah per SOP.',
                'sumber_input: manual / surat / sebelumnya. Langkah pertama tidak boleh sebelumnya.',
                'durasi_hari = bilangan bulat 0 sampai 3650. Contoh alur: 1 manual, 2 sebelumnya, 3 sebelumnya.',
                'Kode SOP yang sudah terdaftar tidak ditimpa. Impor hanya menambah SOP baru.',
                'Jika ada kesalahan pada satu langkah, seluruh file dibatalkan tanpa perubahan data.',
            ];
        } elseif ($jenis === 'kamus') {
            self::buatSheet($buku, 'Kamus Kerja', self::KAMUS);
            $petunjuk = [
                'Sheet Kamus Kerja: satu baris untuk satu nama pekerjaan.',
                'HANYA kolom nama yang wajib. Input, proses, dan output BOLEH KOSONG.',
                'Admin membuat Kamus Kerja kantor; Ketua Tim memilih tim pemilik saat mengimpor pada aplikasi.',
                'Kamus kantor bisa dipilih semua Ketua Tim. Kamus tim hanya digunakan pada tim tersebut.',
                'Pilih Dari Kamus Kerja pada form Beri Tugas untuk mengisi nama/rincian awal yang masih dapat diedit.',
                'Duplikat dalam file maupun duplikat nama pada lingkup yang sama akan ditolak.',
                'Jika ada satu baris bermasalah, seluruh impor dibatalkan tanpa menyimpan sebagian.',
            ];
        } elseif ($jenis === 'tim') {
            self::buatSheet($buku, 'Tim', self::TIM);
            self::buatSheet($buku, 'Anggota Tim', self::ANGGOTA);
            self::buatSheet($buku, 'Daftar Pegawai', ['nip', 'nama', 'role', 'email', 'kode_tim_utama', 'tim_dipimpin', 'tim_sebagai_anggota'],
                User::with(['bagian','tim'])->orderBy('name')->get()->map(fn (User $u) => [
                    $u->nip ?? '', $u->name, $u->role, $u->email, $u->bagian?->kode_bagian ?? '',
                    $u->tim->filter(fn ($t) => $t->pivot->peran_tim === 'ketua')->pluck('kode_bagian')->implode(';'),
                    $u->tim->filter(fn ($t) => $t->pivot->peran_tim === 'anggota')->pluck('kode_bagian')->implode(';'),
                ])->all(), false);
            $petunjuk = [
                'Sheet Tim: satu baris per tim baru; isi kode_tim, nama_tim, dan nip_ketua (opsional).',
                'nip_ketua dapat menunjuk pegawai role staf / kepala_bagian; satu ketua boleh memimpin banyak tim.',
                'Sheet Anggota Tim: satu baris per pasangan kode_tim + nip_pegawai.',
                'kode_tim Anggota Tim harus ada pada sheet Tim (dalam file unggahan yang sama).',
                'Lihat NIP yang valid pada sheet Daftar Pegawai (diisi otomatis sesuai database saat unduh).',
                'Ketua otomatis dimasukkan sebagai anggota meskipun tidak dicantumkan di sheet Anggota Tim.',
                'Satu pegawai dapat menjadi ketua beberapa tim dan anggota pada banyak tim lain sekaligus.',
                'Peran disimpan PER TIM (ketua / anggota), bukan berdasarkan role global akun.',
                'NIP yang panjang WAJIB dalam format TEKS. Jangan edit daftar pegawai untuk membuat akun.',
                'Kode tim yang sudah terdaftar ditolak agar tim lama tidak tertimpa.',
                'Jika ada kesalahan, tidak ada tim atau anggota yang disimpan.',
            ];
        } else {
            abort(404);
        }
        self::buatSheet($buku, 'Petunjuk', ['Panduan pengisian'], array_map(fn ($s) => [$s], $petunjuk), false);
        $buku->setActiveSheetIndex(0);
        $nama = 'template_import_' . $jenis . '.xlsx';

        return response()->streamDownload(function () use ($buku) {
            (new Xlsx($buku))->save('php://output');
            $buku->disconnectWorksheets();
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private static function buatSheet(Spreadsheet $buku, string $nama, array $header, array $baris = [], bool $input = true): void
    {
        $sheet = $buku->createSheet();
        $sheet->setTitle($nama);
        $sheet->getRowDimension(1)->setRowHeight(29);
        foreach ($header as $j => $teks) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1);
            $sheet->setCellValueExplicit($col . '1', $teks, DataType::TYPE_STRING);
            $sheet->getColumnDimension($col)->setWidth(min(42, max(16, strlen($teks) + 8)));
            if (str_contains($teks, 'nama') || in_array($teks, ['proses', 'input', 'output', 'keterangan', 'Panduan pengisian'], true)) {
                $sheet->getColumnDimension($col)->setWidth($teks === 'Panduan pengisian' ? 95 : 38);
            }
            // Format teks penting untuk NIP 18 digit dan kode dengan nol di depan.
            if (str_contains($teks, 'nip') || str_contains($teks, 'kode_')) {
                $sheet->getStyle($col . '2:' . $col . '2000')->getNumberFormat()->setFormatCode('@');
            }
        }
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($header));
        $sheet->getStyle("A1:{$last}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $input ? '1F3864' : '52657B']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$last}1");
        foreach ($baris as $i => $row) {
            foreach ($row as $j => $value) {
                $sheet->setCellValueExplicitByColumnAndRow($j + 1, $i + 2, (string) $value, DataType::TYPE_STRING);
            }
        }
        if ($nama === 'Akun' || $nama === 'SOP') {
            $kolomPilihan = $nama === 'Akun' ? 'D' : 'I';
            $opsi = $nama === 'Akun' ? 'staf,kepala_bagian,kepala_bps,admin' : 'manual,surat,sebelumnya';
            for ($r = 2; $r <= 500; $r++) {
                $pilihan = $sheet->getCell($kolomPilihan . $r)->getDataValidation();
                $pilihan->setType(DataValidation::TYPE_LIST);
                $pilihan->setErrorStyle(DataValidation::STYLE_STOP);
                $pilihan->setAllowBlank(false);
                $pilihan->setShowErrorMessage(true);
                $pilihan->setError('Pilih nilai dari daftar yang disediakan.');
                $pilihan->setShowDropDown(true);
                $pilihan->setFormula1('"' . $opsi . '"');
            }
        }
        if ($input) {
            $sheet->getStyle('A2:' . $last . '100')->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F8FC');
        }
    }

    /** Baris kosong diabaikan. Rumus tidak diterima dan judul kolom harus persis sama. */
    public static function baca(UploadedFile $file, string $namaSheet, array $header, int $batas = 1000): array
    {
        $path = $file->getRealPath();
        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly([$namaSheet]);
            $buku = $reader->load($path);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['file_excel' => 'File Excel tidak dapat dibaca. Gunakan template .xlsx yang disediakan.']);
        }
        $sheet = $buku->getSheetByName($namaSheet);
        if (!$sheet) {
            $buku->disconnectWorksheets();
            throw ValidationException::withMessages(['file_excel' => "Sheet '{$namaSheet}' tidak ada. Gunakan template yang diunduh dari aplikasi."]);
        }
        foreach ($header as $i => $kolom) {
            $actual = trim((string) $sheet->getCellByColumnAndRow($i + 1, 1)->getValue());
            if ($actual !== $kolom) {
                $buku->disconnectWorksheets();
                throw ValidationException::withMessages(['file_excel' => "Header sheet '{$namaSheet}' kolom " . ($i + 1) . " harus '{$kolom}' (ditemukan '{$actual}')."]);
            }
        }
        $last = $sheet->getHighestDataRow();
        if ($last > $batas + 1) {
            $buku->disconnectWorksheets();
            throw ValidationException::withMessages(['file_excel' => "Sheet '{$namaSheet}' maksimal {$batas} baris data."]);
        }
        $hasil = [];
        for ($i = 2; $i <= $last; $i++) {
            $baris = ['_baris' => $i];
            $adaIsi = false;
            foreach ($header as $j => $kunci) {
                $cell = $sheet->getCellByColumnAndRow($j + 1, $i);
                if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                    $buku->disconnectWorksheets();
                    throw ValidationException::withMessages(['file_excel' => "Sheet {$namaSheet} baris {$i}: rumus Excel tidak diizinkan ({$kunci})."]);
                }
                $nilai = $cell->getValue();
                if ($nilai instanceof \DateTimeInterface) $nilai = $nilai->format('Y-m-d');
                if (str_contains($kunci, 'nip') && is_numeric($nilai) && !is_string($nilai) && (float) $nilai >= 1e14) {
                    $buku->disconnectWorksheets();
                    throw ValidationException::withMessages(['file_excel' => "Sheet {$namaSheet} baris {$i}: {$kunci} terbaca sebagai angka panjang dan mungkin kehilangan digit. Formatkan sebagai Teks di Excel lalu isi ulang NIP."]);
                }
                $baris[$kunci] = trim((string) ($nilai ?? ''));
                if ($baris[$kunci] !== '') $adaIsi = true;
            }
            if ($adaIsi) $hasil[] = $baris;
        }
        $buku->disconnectWorksheets();
        return $hasil;
    }
}
