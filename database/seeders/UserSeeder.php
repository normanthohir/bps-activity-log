<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private const JUMLAH_STAF_PER_BAGIAN = 5;

    // Nilai kolom bagian_user.peran_tim untuk ketua tim. Defaultnya di database adalah 'anggota'.
    // GANTI kalau kode tim memakai nilai lain (cek dengan: grep -rn "peran_tim" app resources).
    private const PERAN_KETUA = 'ketua';

    // Jadikan beberapa staf anggota di dua tim sekaligus, untuk menguji keanggotaan lintas tim
    private const BUAT_ANGGOTA_LINTAS_TIM = true;

    private const NAMA_DEPAN = [
        'Yohanes', 'Maria', 'Rahmat', 'Siti', 'Fransiskus', 'Nurhayati',
        'Daniel', 'Aisyah', 'Markus', 'Rosalina', 'Abdullah', 'Theresia',
    ];

    private const NAMA_BELAKANG = [
        'Latuconsina', 'Pattimura', 'Tuasikal', 'Siahaya', 'Ohorella', 'Soplanit',
    ];

    public function run(): void
    {
        $password = Hash::make('password'); // di-hash sekali saja, bcrypt itu lambat
        $nomorNip = 0;
        $nomorStaf = 0;
        $keanggotaan = [];
        $stafPerBagian = [];

        User::create([
            'name' => 'Admin Sistem',
            'nip' => '000000000000000000',
            'email' => 'admin@bps.go.id',
            'password' => $password,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $kepalaBps = User::create([
            'name' => 'Kepala BPS Kota Ambon',
            'nip' => '111111111111111111',
            'email' => 'kepala@bps.go.id',
            'password' => $password,
            'role' => 'kepala_bps',
            'email_verified_at' => now(),
        ]);

        $daftarBagian = DB::table('bagian')->orderBy('id')->get();

        foreach ($daftarBagian as $bagian) {
            $namaSingkat = trim(str_replace(['Sub Bagian', 'Seksi'], '', $bagian->nama_bagian));

            $kepalaTim = User::create([
                'name' => 'Kepala Tim ' . $namaSingkat,
                'nip' => $this->nip(++$nomorNip),
                'email' => 'kepalatim.' . Str::slug($bagian->kode_bagian) . '@bps.go.id',
                'password' => $password,
                'role' => 'kepala_bagian',
                'bagian_id' => $bagian->id,
                'atasan_id' => $kepalaBps->id,
                'email_verified_at' => now(),
            ]);

            DB::table('bagian')->where('id', $bagian->id)->update(['kepala_bagian_id' => $kepalaTim->id]);
            $keanggotaan[] = ['bagian_id' => $bagian->id, 'user_id' => $kepalaTim->id, 'peran_tim' => self::PERAN_KETUA];

            for ($i = 0; $i < self::JUMLAH_STAF_PER_BAGIAN; $i++) {
                $depan = self::NAMA_DEPAN[$nomorStaf % 12];
                $belakang = self::NAMA_BELAKANG[($nomorStaf + intdiv($nomorStaf, 12)) % 6];
                $nomorStaf++;

                $staf = User::create([
                    'name' => "$depan $belakang",
                    'nip' => $this->nip(++$nomorNip),
                    'email' => Str::slug($depan) . '.' . Str::slug($belakang) . '@bps.go.id',
                    'password' => $password,
                    'role' => 'staf',
                    'bagian_id' => $bagian->id,
                    'atasan_id' => $kepalaTim->id,
                    'email_verified_at' => now(),
                ]);

                $keanggotaan[] = ['bagian_id' => $bagian->id, 'user_id' => $staf->id, 'peran_tim' => 'anggota'];
                $stafPerBagian[$bagian->id][] = $staf->id;
            }
        }

        // Anggota lintas tim: staf kedua dari 3 bagian pertama ikut tim bagian berikutnya
        if (self::BUAT_ANGGOTA_LINTAS_TIM && $daftarBagian->count() > 1) {
            $idBagian = $daftarBagian->pluck('id')->values();

            foreach ($idBagian->take(3) as $i => $asal) {
                $tujuan = $idBagian[($i + 1) % $idBagian->count()];

                if (isset($stafPerBagian[$asal][1])) {
                    $keanggotaan[] = ['bagian_id' => $tujuan, 'user_id' => $stafPerBagian[$asal][1], 'peran_tim' => 'anggota'];
                }
            }
        }

        DB::table('bagian_user')->insert($keanggotaan);
    }

    // NIP palsu 18 digit, unik per nomor
    private function nip(int $nomor): string
    {
        return sprintf('1990%014d', $nomor);
    }
}