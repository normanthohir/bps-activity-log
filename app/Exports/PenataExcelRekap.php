<?php

namespace App\Exports\Konsep;

use App\Support\TampilanRekap;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Gaya Excel yang sama untuk semua rekap (judul, kepala tabel, warna predikat, garis). */
trait PenataExcelRekap
{
    private function tulisJudul(Worksheet $s, string $judul, string $periode, string $kolomAkhir): void
    {
        $s->setCellValue('A1', $judul . ' - BPS KOTA AMBON');
        $s->setCellValue('A2', $periode);
        $s->setCellValue('A3', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i'));

        $s->mergeCells("A1:{$kolomAkhir}1");
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1F3864');
        $s->getStyle('A2:A3')->getFont()->getColor()->setARGB('FF64748B');
    }

    private function tulisKepala(Worksheet $s, array $kepala, int $baris): void
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

    private function beriWarna(Worksheet $s, string $sel, string $tingkat): void
    {
        $s->getStyle($sel)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(TampilanRekap::WARNA_EXCEL[$tingkat] ?? TampilanRekap::WARNA_EXCEL['kosong']);
    }

    private function rapikanSheet(Worksheet $s, string $dari, string $sampai, int $barisKepala, int $barisAkhir, string $bekukan): void
    {
        foreach (range($dari, $sampai) as $kolom) {
            $s->getColumnDimension($kolom)->setAutoSize(true);
        }

        if ($barisAkhir > $barisKepala) {
            $s->getStyle("{$dari}{$barisKepala}:{$sampai}{$barisAkhir}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB('FFE2E8F0');
        }

        $s->freezePane($bekukan);
    }
}