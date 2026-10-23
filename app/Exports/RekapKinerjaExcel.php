<?php

namespace App\Exports;

use App\Services\RekapKinerjaService;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapKinerjaExcel
{
    // Warna latar sel predikat (ARGB)
    private const WARNA = [
        'sangat_baik' => 'FFDCFCE7',
        'baik' => 'FFDBEAFE',
        'cukup' => 'FFFEF3C7',
        'kurang' => 'FFFEE2E2',
        'kosong' => 'FFF1F5F9',
    ];

    public function unduh(array $rekap, string $namaFile): StreamedResponse
    {
        $buku = new Spreadsheet();

        $this->sheetPegawai($buku->getActiveSheet(), $rekap);
        $this->sheetBagian($buku->createSheet(), $rekap);
        $buku->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($buku) {
            (new Xlsx($buku))->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function sheetPegawai(Worksheet $s, array $rekap): void
    {
        $s->setTitle('Per Pegawai');
        $this->judul($s, 'REKAP KINERJA PEGAWAI', $rekap, 'O');

        $kepala = ['Peringkat', 'Nama', 'NIP', 'Peran', 'Bagian', 'Hari Lapor', 'Total Laporan',
            'Disetujui', 'Dikembalikan', 'Menunggu', 'Output Selesai', 'Output Tertunda', 'Output Gagal',
            'Skor', 'Predikat'];
        $this->kepalaTabel($s, $kepala, 5);

        $baris = 6;
        foreach ($rekap['pegawai'] as $p) {
            $s->setCellValue("A{$baris}", $p['peringkat']);
            $s->setCellValue("B{$baris}", $p['nama']);
            // NIP 18 digit harus disimpan sebagai teks, kalau tidak Excel membulatkannya
            $s->setCellValueExplicit("C{$baris}", (string) $p['nip'], DataType::TYPE_STRING);
            $s->setCellValue("D{$baris}", $p['peran']);
            $s->setCellValue("E{$baris}", $p['bagian']);
            $s->setCellValue("F{$baris}", $p['hari_lapor']);
            $s->setCellValue("G{$baris}", $p['total']);
            $s->setCellValue("H{$baris}", $p['disetujui']);
            $s->setCellValue("I{$baris}", $p['dikembalikan']);
            $s->setCellValue("J{$baris}", $p['menunggu']);
            $s->setCellValue("K{$baris}", $p['selesai']);
            $s->setCellValue("L{$baris}", $p['tertunda']);
            $s->setCellValue("M{$baris}", $p['gagal']);
            $s->setCellValue("N{$baris}", $p['skor'] ?? '-');
            $s->setCellValue("O{$baris}", RekapKinerjaService::LABEL[$p['tingkat']]);

            $s->getStyle("N{$baris}")->getNumberFormat()->setFormatCode('0.0');
            $this->warnai($s, "O{$baris}", $p['tingkat']);
            $baris++;
        }

        $this->rapikan($s, 'A', 'O', 5, $baris - 1, 'C6');
    }

    private function sheetBagian(Worksheet $s, array $rekap): void
    {
        $s->setTitle('Per Bagian');
        $this->judul($s, 'REKAP KINERJA PER BAGIAN', $rekap, 'K');

        $kepala = ['Bagian', 'Kode', 'Kepala Tim', 'Jumlah Pegawai', 'Sudah Melapor', 'Belum Melapor',
            'Total Laporan', 'Disetujui', 'Dikembalikan', 'Rata-rata Skor', 'Predikat'];
        $this->kepalaTabel($s, $kepala, 5);

        $baris = 6;
        foreach ($rekap['bagian'] as $b) {
            $s->setCellValue("A{$baris}", $b['nama']);
            $s->setCellValue("B{$baris}", $b['kode']);
            $s->setCellValue("C{$baris}", $b['kepala']);
            $s->setCellValue("D{$baris}", $b['jumlah_pegawai']);
            $s->setCellValue("E{$baris}", $b['melapor']);
            $s->setCellValue("F{$baris}", $b['belum_lapor']);
            $s->setCellValue("G{$baris}", $b['total']);
            $s->setCellValue("H{$baris}", $b['disetujui']);
            $s->setCellValue("I{$baris}", $b['dikembalikan']);
            $s->setCellValue("J{$baris}", $b['skor'] ?? '-');
            $s->setCellValue("K{$baris}", RekapKinerjaService::LABEL[$b['tingkat']]);

            $s->getStyle("J{$baris}")->getNumberFormat()->setFormatCode('0.0');
            $this->warnai($s, "K{$baris}", $b['tingkat']);
            $baris++;
        }

        $this->rapikan($s, 'A', 'K', 5, $baris - 1, 'A6');
    }

    private function judul(Worksheet $s, string $judul, array $rekap, string $kolomAkhir): void
    {
        $p = $rekap['periode'];

        $s->setCellValue('A1', $judul . ' - BPS KOTA AMBON');
        $s->setCellValue('A2', 'Periode: ' . $p['label'] . '   |   Hari kerja: ' . $p['hari_kerja'] . ' (Senin-Jumat)');
        $s->setCellValue('A3', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i'));

        $s->mergeCells("A1:{$kolomAkhir}1");
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1F3864');
        $s->getStyle('A2:A3')->getFont()->getColor()->setARGB('FF64748B');
    }

    private function kepalaTabel(Worksheet $s, array $kepala, int $baris): void
    {
        $s->fromArray($kepala, null, "A{$baris}");
        $akhir = $s->getCell([count($kepala), $baris])->getColumn();

        $gaya = $s->getStyle("A{$baris}:{$akhir}{$baris}");
        $gaya->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $gaya->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F3864');
        $gaya->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $s->getRowDimension($baris)->setRowHeight(32);
    }

    private function warnai(Worksheet $s, string $sel, string $tingkat): void
    {
        $s->getStyle($sel)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::WARNA[$tingkat]);
    }

    private function rapikan(Worksheet $s, string $dari, string $sampai, int $barisKepala, int $barisAkhir, string $bekukan): void
    {
        foreach (range($dari, $sampai) as $kolom) {
            $s->getColumnDimension($kolom)->setAutoSize(true);
        }

        if ($barisAkhir > $barisKepala) {
            $s->getStyle("{$dari}{$barisKepala}:{$sampai}{$barisAkhir}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB('FFE2E8F0');
        }

        $s->freezePane($bekukan);
    }
}