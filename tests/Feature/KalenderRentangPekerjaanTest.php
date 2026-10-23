<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KalenderRentangPekerjaanTest extends TestCase
{
    use RefreshDatabase;

    private function tugas(User $kepala, User $penerima, Bagian $tim, string $judul, ?string $mulai, ?string $tenggat): Tugas
    {
        return Tugas::create([
            'dibuat_oleh' => $kepala->id,
            'ditugaskan_ke' => $penerima->id,
            'bagian_id' => $tim->id,
            'judul' => $judul,
            'tanggal_mulai' => $mulai,
            'tenggat' => $tenggat,
            'status' => 'belum_dikerjakan',
        ]);
    }

    public function test_pekerjaan_lintas_bulan_dan_minggu_menjadi_batang_berkelanjutan(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $staf = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $tugas = $this->tugas($kepala, $staf, $tim, 'Penyusunan Publikasi', '2026-09-28', '2026-10-07');

        $response = $this->actingAs($staf)->get(route('dashboard', ['bulan' => '2026-10']));
        $response->assertOk()->assertSee('Penyusunan Publikasi');
        $response->assertViewHas('kalender', function (array $kalender) use ($tugas) {
            $segmen = $kalender['minggu']->flatMap(fn ($m) => $m['segmen'])
                ->filter(fn ($s) => $s['tugas']->id === $tugas->id)->values();
            return $kalender['jumlah'] === 1
                && $segmen->count() === 2
                && $segmen[0]['kolom_mulai'] === 1 && $segmen[0]['panjang'] === 7
                && $segmen[0]['lanjut_minggu_depan']
                && $segmen[1]['kolom_mulai'] === 1 && $segmen[1]['panjang'] === 3
                && $segmen[1]['lanjut_dari_minggu_lalu'];
        });
    }

    public function test_rentang_bertabrakan_tidak_diletakkan_pada_baris_yang_sama(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $staf = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $tugasA = $this->tugas($kepala, $staf, $tim, 'Kerja A', '2026-10-12', '2026-10-16');
        $tugasB = $this->tugas($kepala, $staf, $tim, 'Kerja B', '2026-10-14', '2026-10-18');
        $tugasC = $this->tugas($kepala, $staf, $tim, 'Kerja C', '2026-10-19', '2026-10-19');
        $response = $this->actingAs($staf)->get(route('dashboard', ['bulan' => '2026-10']));
        $response->assertOk()->assertViewHas('kalender', function (array $kalender) use ($tugasA, $tugasB, $tugasC) {
            $minggu = $kalender['minggu']->first(fn ($m) => $m['hari'][0]['tanggal']->toDateString() === '2026-10-12');
            $a = $minggu['segmen']->first(fn ($segmen) => $segmen['tugas']->id === $tugasA->id);
            $b = $minggu['segmen']->first(fn ($segmen) => $segmen['tugas']->id === $tugasB->id);
            $semua = $kalender['minggu']->flatMap(fn ($m) => $m['segmen']);
            return $kalender['jumlah'] === 3
                && $a['panjang'] === 5 && $b['panjang'] === 5
                && $a['baris'] !== $b['baris']
                && $semua->contains(fn ($s) => $s['tugas']->id === $tugasC->id && $s['panjang'] === 1);
        });
    }

    public function test_pekerjaan_orang_lain_tidak_muncul_dalam_kalender_staf(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $stafA = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $stafB = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $this->tugas($kepala, $stafA, $tim, 'Tugas Pribadi', '2026-10-10', '2026-10-13');
        $this->tugas($kepala, $stafB, $tim, 'Tugas Rahasia', '2026-10-10', '2026-10-13');

        $this->actingAs($stafA)->get(route('dashboard', ['bulan' => '2026-10']))
            ->assertOk()->assertSee('Tugas Pribadi')->assertDontSee('Tugas Rahasia')
            ->assertViewHas('kalender', fn (array $kalender) => $kalender['jumlah'] === 1);
    }

    public function test_tugas_tanpa_tenggat_tetap_muncul_pada_tanggal_mulai(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $staf = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $this->tugas($kepala, $staf, $tim, 'Pekerjaan Sehari', '2026-10-20', null);
        $this->actingAs($staf)->get(route('dashboard', ['bulan' => '2026-10']))
            ->assertOk()->assertViewHas('kalender', function (array $kalender) {
                $segmen = $kalender['minggu']->flatMap(fn ($m) => $m['segmen']);
                return $kalender['jumlah'] === 1 && $segmen->count() === 1
                    && $segmen->first()['panjang'] === 1;
            });
    }

    public function test_maksimal_lima_batang_memprioritaskan_sop_dan_menampilkan_sisanya_per_tanggal(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $pegawai = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $sop = \App\Models\Sop::create([
            'kode' => 'CAL-01', 'nama' => 'SOP Kalibrasi', 'dibuat_oleh' => $kepala->id,
        ]);
        $pelaksanaan = \App\Models\SopPelaksanaan::create([
            'sop_id' => $sop->id, 'dibuat_oleh' => $kepala->id, 'nama' => 'Pelaksanaan Kalender',
        ]);
        $induk = $this->tugas($kepala, $pegawai, $tim, 'Induk SOP Utama', '2026-10-12', '2026-10-16');
        $induk->update(['sop_pelaksanaan_id' => $pelaksanaan->id]);
        $lainnya = [];
        for ($n = 1; $n <= 6; $n++) {
            $lainnya[] = $this->tugas($kepala, $pegawai, $tim, 'Tugas Biasa '.$n, '2026-10-12', '2026-10-16');
        }

        $this->actingAs($pegawai)->get(route('dashboard', ['bulan' => '2026-10']))
            ->assertOk()
            ->assertSee('data-rapi-kalender-more="2026-10-12"', false)
            ->assertSee('+2 lainnya')
            ->assertViewHas('kalender', function (array $kalender) use ($induk) {
                $pekan = $kalender['minggu']->first(fn ($m) => $m['hari'][0]['tanggal']->toDateString() === '2026-10-12');
                $terlihat = $pekan['segmen'];
                $tersembunyi = $kalender['daftar_tersembunyi']['2026-10-12'] ?? [];
                return $kalender['jumlah'] === 7
                    && $pekan['jumlah_baris'] === 5
                    && $terlihat->count() === 5
                    && $terlihat->contains(fn ($s) => $s['tugas']->id === $induk->id && $s['baris'] === 2)
                    && count($tersembunyi) === 2
                    && collect($tersembunyi)->every(fn ($t) => !$t['sop'])
                    && count($kalender['daftar_tersembunyi']['2026-10-16']) === 2;
            });
    }

    public function test_daftar_lainnya_tidak_bocor_ke_akun_lain(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $pegawaiA = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        $pegawaiB = User::factory()->create(['role' => 'staf', 'bagian_id' => $tim->id]);
        for ($n = 0; $n < 7; $n++) {
            $this->tugas($kepala, $pegawaiB, $tim, 'Tugas Tertutup '.$n, '2026-10-12', '2026-10-16');
        }
        $this->actingAs($pegawaiA)->get(route('dashboard', ['bulan' => '2026-10']))
            ->assertOk()
            ->assertDontSee('Tugas Tertutup')
            ->assertDontSee('data-rapi-kalender-more="2026-10-12"', false)
            ->assertViewHas('kalender', fn (array $kalender) => $kalender['jumlah'] === 0 && empty($kalender['daftar_tersembunyi']));
    }

}
