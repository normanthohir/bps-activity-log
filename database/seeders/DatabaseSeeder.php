<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting: Bagian dulu, baru User
        // (karena User butuh bagian_id yang valid)
        $this->call([
            BagianSeeder::class,
            UserSeeder::class,
        ]);
    }
}
