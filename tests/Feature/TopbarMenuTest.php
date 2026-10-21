<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\User;
use App\Support\KeanggotaanTim;
use App\Support\MenuNavigasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopbarMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_kamus_dan_administrasi_tanpa_monitoring_pegawai(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('data-nav-group="pekerjaan"', false)
            ->assertSee('data-nav-group="standar"', false)
            ->assertSee('data-nav-group="administrasi"', false)
            ->assertSee('data-nav-link="kamus.index"', false)
            ->assertDontSee('data-nav-group="monitoring"', false)
            ->assertDontSee('data-nav-link="kabag.tugas.index"', false);
    }

    public function test_pegawai_mode_anggota_hanya_melihat_menu_pekerjaan_pribadi(): void
    {
        $pegawai = User::factory()->create(['role' => 'staf']);

        $this->actingAs($pegawai)->get(route('pekerjaan.index'))
            ->assertOk()
            ->assertSee('data-nav-link="staf.tugas.index"', false)
            ->assertSee('data-nav-link="laporan.index"', false)
            ->assertDontSee('data-nav-link="pekerjaan.create"', false)
            ->assertDontSee('data-nav-link="kamus.index"', false)
            ->assertDontSee('data-nav-link="admin.users.index"', false)
            ->assertDontSee('data-nav-link="kabag.approval.index"', false);
    }

    public function test_ketua_mode_ketua_mendapat_menu_tim_dan_beralih_ke_anggota(): void
    {
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim 01']);
        $pegawai = User::factory()->create(['role' => 'staf']);
        KeanggotaanTim::tetapkanKetua($tim, $pegawai);

        $this->actingAs($pegawai)->post(route('mode-kerja.ganti'), ['mode' => 'ketua'])
            ->assertSessionHas('mode_kerja', 'ketua');
        $this->get(route('pekerjaan.index'))
            ->assertOk()
            ->assertSee('data-nav-link="pekerjaan.create"', false)
            ->assertSee('data-nav-link="kabag.tugas.index"', false)
            ->assertSee('data-nav-link="kabag.approval.index"', false)
            ->assertSee('data-nav-link="kamus.index"', false)
            ->assertDontSee('data-nav-link="admin.bagian.index"', false);

        $this->post(route('mode-kerja.ganti'), ['mode' => 'anggota'])
            ->assertSessionHas('mode_kerja', 'anggota');
        $this->get(route('pekerjaan.index'))
            ->assertOk()
            ->assertDontSee('data-nav-link="kabag.tugas.index"', false)
            ->assertSee('data-nav-link="staf.tugas.index"', false);
    }

    public function test_kepala_bps_melihat_monitoring_dan_penugasan_kepala(): void
    {
        $kepala = User::factory()->create(['role' => 'kepala_bps']);

        $this->actingAs($kepala)->get(route('pekerjaan.index'))
            ->assertOk()
            ->assertSee('data-nav-link="kepala-bps.rekap"', false)
            ->assertSee('data-nav-link="kepala-bps.approval.index"', false)
            ->assertSee('data-nav-link="kepala-bps.tugas.index"', false)
            ->assertDontSee('data-nav-link="admin.users.index"', false);
    }

    public function test_semua_kelompok_punya_route_terdaftar_dan_pencocokan_aktif_tepat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (MenuNavigasi::kelompok($admin) as $bagian) {
            foreach ($bagian['items'] as $tautan) {
                $this->assertTrue(\Illuminate\Support\Facades\Route::has($tautan['route']), $tautan['route']);
            }
        }

        $this->assertTrue(MenuNavigasi::cocok('pekerjaan.create', ['pekerjaan']));
        $this->assertTrue(MenuNavigasi::cocok('sop.index', ['sop']));
        $this->assertFalse(MenuNavigasi::cocok('kabag.tugas-aktif.index', ['kabag.tugas']));
        $this->assertFalse(MenuNavigasi::cocok('admin.users.index', ['pekerjaan', 'sop']));
    }
}
