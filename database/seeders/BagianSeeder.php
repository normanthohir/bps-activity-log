<?php

namespace Database\Seeders;

use App\Models\Bagian;
use Illuminate\Database\Seeder;

class BagianSeeder extends Seeder
{
    public function run(): void
    {
        $daftarBagian = [
            ['nama_bagian' => 'Sub Bagian Umum', 'kode_bagian' => 'UMUM'],
            ['nama_bagian' => 'Seksi Statistik Sosial', 'kode_bagian' => 'STAT-SOS'],
            ['nama_bagian' => 'Seksi Statistik Produksi', 'kode_bagian' => 'STAT-PROD'],
            ['nama_bagian' => 'Seksi Statistik Distribusi', 'kode_bagian' => 'STAT-DIST'],
            ['nama_bagian' => 'Seksi IPDS', 'kode_bagian' => 'IPDS'],
            ['nama_bagian' => 'Seksi Neraca Wilayah dan Analisis Statistik', 'kode_bagian' => 'NWAS'],
        ];

        foreach ($daftarBagian as $bagian) {
            Bagian::create($bagian);
        }
    }
}
