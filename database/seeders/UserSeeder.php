<?php

namespace Database\Seeders;

use App\Models\Bagian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $statSos = Bagian::where('kode_bagian', 'STAT-SOS')->first();
        $statProd = Bagian::where('kode_bagian', 'STAT-PROD')->first();

        // ── Admin ────────────────────────────────────────
        User::create([
            'name' => 'Admin Sistem',
            'nip' => '000000000000000000',
            'email' => 'admin@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'bagian_id' => null,
            'email_verified_at' => now(),
        ]);

        // ── Kepala BPS Kota Ambon ───────────────────────
        $kepalaBps = User::create([
            'name' => 'Kepala BPS Kota Ambon',
            'nip' => '111111111111111111',
            'email' => 'kepala@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'kepala_bps',
            'bagian_id' => null,
            'email_verified_at' => now(),
        ]);

        // ── Kepala Seksi Statistik Sosial ───────────────
        $kabagSos = User::create([
            'name' => 'Siti Kepala Seksi Sosial',
            'nip' => '222222222222222222',
            'email' => 'kabag.sosial@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'kepala_bagian',
            'bagian_id' => $statSos->id,
            'atasan_id' => $kepalaBps->id,
            'email_verified_at' => now(),
        ]);

        // Set kepala_bagian_id di tabel bagian setelah user-nya ada
        $statSos->update(['kepala_bagian_id' => $kabagSos->id]);

        // ── Kepala Seksi Statistik Produksi ─────────────
        $kabagProd = User::create([
            'name' => 'Andi Kepala Seksi Produksi',
            'nip' => '333333333333333333',
            'email' => 'kabag.produksi@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'kepala_bagian',
            'bagian_id' => $statProd->id,
            'atasan_id' => $kepalaBps->id,
            'email_verified_at' => now(),
        ]);

        $statProd->update(['kepala_bagian_id' => $kabagProd->id]);

        // ── Staf di Seksi Statistik Sosial ──────────────
        User::create([
            'name' => 'Budi Staf Sosial',
            'nip' => '444444444444444444',
            'email' => 'budi@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'bagian_id' => $statSos->id,
            'atasan_id' => $kabagSos->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Ani Staf Sosial',
            'nip' => '555555555555555555',
            'email' => 'ani@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'bagian_id' => $statSos->id,
            'atasan_id' => $kabagSos->id,
            'email_verified_at' => now(),
        ]);

        // ── Staf di Seksi Statistik Produksi ────────────
        User::create([
            'name' => 'Dedi Staf Produksi',
            'nip' => '666666666666666666',
            'email' => 'dedi@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'bagian_id' => $statProd->id,
            'atasan_id' => $kabagProd->id,
            'email_verified_at' => now(),
        ]);
    }
}
