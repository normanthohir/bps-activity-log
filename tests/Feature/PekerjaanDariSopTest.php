<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Models\Sop;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Penugasan SOP sekarang hanya melalui halaman delegasi SOP; form umum memakai Kamus Kerja. */
class PekerjaanDariSopTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_umum_hanya_menawarkan_manual_atau_kamus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('pekerjaan.create'))->assertOk()
            ->assertSee('Pilih dari Kamus Kerja')->assertDontSee('Pilih dari SOP');
    }

    public function test_judul_dan_rincian_dari_kamus_boleh_diedit_dan_tanpa_input_output(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tim = Bagian::create(['nama_bagian' => 'Tim A', 'kode_bagian' => '01']);
        $anggota = User::factory()->create(['role' => 'staf']);
        $anggota->tim()->attach($tim->id);
        $kamus = KamusKerja::create(['nama' => 'Validasi Data', 'dibuat_oleh' => $admin->id]);
        $this->actingAs($admin)->post(route('pekerjaan.store'), [
            'sumber_judul' => 'kamus', 'kamus_kerja_id' => $kamus->id,
            'judul' => 'Validasi Berkas 2026', 'bagian_id' => $tim->id,
            'penerima_ids' => [$anggota->id], 'jenis_input' => 'manual',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $tugas = Tugas::firstOrFail();
        $this->assertSame('Validasi Berkas 2026', $tugas->judul);
        $this->assertSame('Validasi Berkas 2026', $tugas->deskripsi);
        $this->assertNull($tugas->uraian_input);
        $this->assertNull($tugas->output_rencana);
        $this->assertSame($kamus->id, $tugas->kamus_kerja_id);
        $this->assertNull($tugas->sop_langkah_id);
    }

    public function test_kamus_tim_lain_tidak_boleh_dipakai_untuk_tugas_tim_berbeda(): void
    {
        $ketua = User::factory()->create(['role' => 'staf']);
        $a = Bagian::create(['nama_bagian' => 'Tim A', 'kode_bagian' => 'A']);
        $b = Bagian::create(['nama_bagian' => 'Tim B', 'kode_bagian' => 'B']);
        KeanggotaanTim::tetapkanKetua($a, $ketua);
        KeanggotaanTim::tetapkanKetua($b, $ketua);
        $kamus = KamusKerja::create(['nama' => 'Kamus A', 'bagian_id' => $a->id, 'dibuat_oleh' => $ketua->id]);
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode' => 'ketua']);
        $this->post(route('pekerjaan.store'), [
            'sumber_judul' => 'kamus', 'kamus_kerja_id' => $kamus->id,
            'judul' => 'Salah Tim', 'bagian_id' => $b->id,
            'penerima_ids' => [$ketua->id], 'jenis_input' => 'manual',
        ])->assertSessionHasErrors('kamus_kerja_id');
        $this->assertDatabaseCount('tugas', 0);
    }
}
