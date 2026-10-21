<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurSopMenuDanRincianTest extends TestCase
{
    use RefreshDatabase;

    private function dataSop(string $proses = 'Validasi'): array
    {
        return ['kode' => 'PROSES-A', 'nama' => 'Pengolahan data', 'versi' => '1.0',
            'langkah' => [0 => ['input' => 'Data awal', 'proses' => $proses,
                'output' => 'Data valid', 'durasi_hari' => 2, 'sumber_input' => 'manual']]];
    }

    public function test_admin_buat_sop_kantor_kepala_delegasi_dan_ketua_beri_tugas_dari_menu_sop(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $ketua = User::factory()->create(['role' => 'staf']);
        $anggota = User::factory()->create(['role' => 'staf']);
        $luar = User::factory()->create(['role' => 'staf']);
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $lain = Bagian::create(['kode_bagian' => '02', 'nama_bagian' => 'Tim B']);
        KeanggotaanTim::tetapkanKetua($tim, $ketua);
        $anggota->tim()->syncWithoutDetaching([$tim->id => ['peran_tim' => 'anggota']]);
        $luar->tim()->syncWithoutDetaching([$lain->id => ['peran_tim' => 'anggota']]);

        $this->actingAs($admin)->post(route('sop.store'), $this->dataSop())
            ->assertRedirect()->assertSessionHasNoErrors();
        $sop = Sop::firstOrFail();
        $this->assertNull($sop->bagian_id);
        $this->actingAs($kepala)->post(route('sop.delegasi.simpan', $sop), [
            'nama' => 'SOP Oktober', 'bagian_id' => $tim->id,
            'ketua_id' => $ketua->id, 'tanggal_mulai' => '2026-10-21', 'jenis_input' => 'manual',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $induk = Tugas::whereNull('sop_langkah_id')->firstOrFail();
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode' => 'ketua']);
        $this->get(route('sop.index'))->assertOk()->assertSee('Beri Tugas: SOP Oktober');
        $this->get(route('pekerjaan.create', [
            'pelaksanaan' => $induk->sop_pelaksanaan_id, 'langkah' => $sop->langkah->first()->id,
        ]))->assertOk()->assertSee('Data awal')->assertSee('Validasi')->assertSee('Data valid');
        $this->post(route('sop.delegasi.simpan-langkah', $induk->sopPelaksanaan), [
            'sop_langkah_id' => $sop->langkah->first()->id,
            'penerima_ids' => [$ketua->id, $anggota->id],
            'judul' => 'Judul yang sudah diedit', 'uraian_input' => 'Bahan khusus',
            'deskripsi' => 'Verifikasi tambahan', 'output_rencana' => 'Berkas terverifikasi',
            'tanggal_mulai' => '2026-10-21', 'tenggat' => '2026-10-23',
        ])->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing([$ketua->id, $anggota->id],
            Tugas::where('sop_langkah_id', $sop->langkah->first()->id)->pluck('ditugaskan_ke')->all());
        $tugas = Tugas::where('sop_langkah_id', $sop->langkah->first()->id)->firstOrFail();
        $this->assertSame('Bahan khusus', $tugas->uraian_input);
        $this->get(route('pekerjaan.edit-rincian', $tugas))->assertOk();
        $this->patch(route('pekerjaan.update-rincian', $tugas), [
            'judul' => 'Revisi penugasan', 'deskripsi' => 'Periksa silang',
            'uraian_input' => 'Data masuk versi B', 'output_rencana' => 'File B',
            'tanggal_mulai' => '2026-10-21', 'tenggat' => '2026-10-25',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Revisi penugasan', $tugas->fresh()->judul);
        $this->assertSame('Data masuk versi B', $tugas->fresh()->uraian_input);
        $this->assertSame('Validasi', $sop->langkah->first()->proses);
        $this->post(route('sop.delegasi.simpan-langkah', $induk->sopPelaksanaan), [
            'sop_langkah_id' => $sop->langkah->first()->id, 'penerima_ids' => [$luar->id],
            'tanggal_mulai' => '2026-10-21', 'tenggat' => '2026-10-23',
        ])->assertSessionHasErrors('penerima_ids');
    }

    public function test_ketua_buat_sop_wajib_pilih_tim_dipimpin_dan_bisa_langsung_bagikan(): void
    {
        $ketua = User::factory()->create(['role' => 'staf']);
        $anggota = User::factory()->create(['role' => 'staf']);
        $a = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $b = Bagian::create(['kode_bagian' => '02', 'nama_bagian' => 'Tim B']);
        $luar = Bagian::create(['kode_bagian' => '03', 'nama_bagian' => 'Tim Luar']);
        KeanggotaanTim::tetapkanKetua($a, $ketua);
        KeanggotaanTim::tetapkanKetua($b, $ketua);
        $anggota->tim()->syncWithoutDetaching([$a->id => ['peran_tim' => 'anggota']]);
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode' => 'ketua']);
        $this->post(route('sop.store'), $this->dataSop())->assertSessionHasErrors('bagian_id');
        $this->post(route('sop.store'), [...$this->dataSop(), 'bagian_id' => $luar->id])
            ->assertForbidden();
        $this->post(route('sop.store'), [...$this->dataSop(), 'bagian_id' => $a->id])
            ->assertSessionHasNoErrors();
        $sop = Sop::firstOrFail();
        $this->assertSame($a->id, $sop->bagian_id);
        $this->post(route('sop.mulai-tim', $sop), [
            'nama' => 'SOP milik Tim A', 'tanggal_mulai' => '2026-10-21',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $induk = Tugas::whereNull('sop_langkah_id')->firstOrFail();
        $this->assertSame($ketua->id, $induk->ditugaskan_ke);
        $this->assertSame($a->id, $induk->bagian_id);
        $this->assertSame(1, Tugas::count());
        $this->get(route('pekerjaan.create', ['pelaksanaan' => $induk->sop_pelaksanaan_id,
            'langkah' => $sop->langkah->first()->id]))->assertOk()->assertSee($anggota->name);
        $this->post(route('sop.delegasi.simpan-langkah', $induk->sopPelaksanaan), [
            'sop_langkah_id' => $sop->langkah->first()->id,
            'penerima_ids' => [$anggota->id, $ketua->id],
            'tanggal_mulai' => '2026-10-21', 'tenggat' => '2026-10-22',
        ])->assertSessionHasNoErrors();
        $this->assertSame(3, Tugas::count());
        $this->actingAs(User::factory()->create(['role' => 'kepala_bps']))
            ->get(route('sop.delegasi.buat', $sop))->assertForbidden();
    }

    public function test_pegawai_mode_anggota_tidak_dapat_mendeklarasikan_sop(): void
    {
        $staf = User::factory()->create(['role' => 'staf']);
        $this->actingAs($staf)->get(route('sop.create'))->assertForbidden();
        $this->post(route('sop.store'), $this->dataSop())->assertForbidden();
    }
}
