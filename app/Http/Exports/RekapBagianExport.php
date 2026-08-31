<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBagianExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected Collection $data)
    {
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Bagian', 'Kode', 'Jumlah Pegawai', 'Total Laporan', 'Disetujui', 'Menunggu', 'Dikembalikan'];
    }

    public function map($bagian): array
    {
        return [
            $bagian->nama_bagian,
            $bagian->kode_bagian,
            $bagian->pegawai_count,
            $bagian->total_laporan,
            $bagian->disetujui,
            $bagian->menunggu,
            $bagian->dikembalikan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F3864'],
                ],
            ],
        ];
    }
}