<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting: bagian -> user -> kamus kerja -> tugas & laporan
        $this->call([
            BagianSeeder::class,
            UserSeeder::class,
            KamusKerjaSeeder::class,
            TugasLaporanSeeder::class,
        ]);
    }
}