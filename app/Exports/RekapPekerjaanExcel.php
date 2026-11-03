<?php

namespace App\Exports;

use App\Exports\Konsep\PenataExcelRekap;
use App\Services\RekapPekerjaanService as Rekap;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapPekerjaanExcel
{
    use PenataExcelRekap;

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
        $this->tulisJudul($s, 'REKAP PEKERJAAN PEGAWAI', 'Periode: ' . $rekap['periode']['label'], 'N');

        $this->tulisKepala($s, [
            'Peringkat', 'Nama', 'NIP', 'Peran', 'Bagian', 'Total Tugas', 'Selesai', 'Dikerjakan',
            'Belum Dikerjakan', 'Terlambat', 'Selesai Tepat Waktu', 'Sudah Dilaporkan', 'Skor', 'Predikat',
        ], 5);

        $baris = 6;
        foreach ($rekap['pegawai'] as $p) {
            $s->setCellValue("A{$baris}", $p['peringkat']);
            $s->setCellValue("B{$baris}", $p['nama']);
            // NIP 18 digit harus disimpan sebagai teks, kalau tidak Excel membulatkannya
            $s->setCellValueExplicit("C{$baris}", (string) $p['nip'], DataType::TYPE_STRING);
            $s->setCellValue("D{$baris}", $p['peran']);
            $s->setCellValue("E{$baris}", $p['bagian']);
            $s->setCellValue("F{$baris}", $p['total']);
            $s->setCellValue("G{$baris}", $p['selesai']);
            $s->setCellValue("H{$baris}", $p['dikerjakan']);
            $s->setCellValue("I{$baris}", $p['belum']);
            $s->setCellValue("J{$baris}", $p['terlambat']);
            $s->setCellValue("K{$baris}", $p['tepat']);
            $s->setCellValue("L{$baris}", $p['terlapor']);
            $s->setCellValue("M{$baris}", $p['skor'] ?? '-');
            $s->setCellValue("N{$baris}", Rekap::LABEL[$p['tingkat']]);

            $s->getStyle("M{$baris}")->getNumberFormat()->setFormatCode('0.0');
            $this->beriWarna($s, "N{$baris}", $p['tingkat']);
            $baris++;
        }

        $this->rapikanSheet($s, 'A', 'N', 5, $baris - 1, 'C6');
    }

    private function sheetBagian(Worksheet $s, array $rekap): void
    {
        $s->setTitle('Per Bagian');
        $this->tulisJudul($s, 'REKAP PEKERJAAN PER BAGIAN', 'Periode: ' . $rekap['periode']['label'], 'I');

        $this->tulisKepala($s, [
            'Bagian', 'Kode', 'Kepala Tim', 'Anggota', 'Total Tugas', 'Selesai', 'Terlambat', 'Skor', 'Predikat',
        ], 5);

        $baris = 6;
        foreach ($rekap['bagian'] as $b) {
            $s->setCellValue("A{$baris}", $b['nama']);
            $s->setCellValue("B{$baris}", $b['kode']);
            $s->setCellValue("C{$baris}", $b['kepala']);
            $s->setCellValue("D{$baris}", $b['anggota']);
            $s->setCellValue("E{$baris}", $b['total']);
            $s->setCellValue("F{$baris}", $b['selesai']);
            $s->setCellValue("G{$baris}", $b['terlambat']);
            $s->setCellValue("H{$baris}", $b['skor'] ?? '-');
            $s->setCellValue("I{$baris}", Rekap::LABEL[$b['tingkat']]);

            $s->getStyle("H{$baris}")->getNumberFormat()->setFormatCode('0.0');
            $this->beriWarna($s, "I{$baris}", $b['tingkat']);
            $baris++;
        }

        $this->rapikanSheet($s, 'A', 'I', 5, $baris - 1, 'A6');
    }
}