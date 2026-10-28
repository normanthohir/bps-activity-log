<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegasiSopUtuhTest extends TestCase
{
    use RefreshDatabase;

    private function siapkan(): array
    {
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $timA = Bagian::create(['kode_bagian'=>'02', 'nama_bagian'=>'Tim B']);
        $timB = Bagian::create(['kode_bagian'=>'01', 'nama_bagian'=>'Tim A']);
        $ketua = User::factory()->create(['role'=>'staf']);
        $anggota = User::factory()->create(['role'=>'staf']);
        $anggotaLain = User::factory()->create(['role'=>'staf']);
        KeanggotaanTim::tetapkanKetua($timB, $ketua);
        $anggota->tim()->syncWithoutDetaching([$timB->id => ['peran_tim' => 'anggota']]);
        $anggotaLain->tim()->syncWithoutDetaching([$timA->id => ['peran_tim' => 'anggota']]);
        $sop = Sop::create(['kode'=>'SOP-X','nama'=>'Pengolahan','versi'=>'1.0','dibuat_oleh'=>$kepala->id]);
        $satu = $sop->langkah()->create(['urutan'=>1,'input'=>'Data mentah','proses'=>'Validasi data','output'=>'Data valid','sumber_input'=>'manual','durasi_hari'=>2]);
        $dua = $sop->langkah()->create(['urutan'=>2,'input'=>'Data valid','proses'=>'Analisis','output'=>'Laporan','sumber_input'=>'sebelumnya','durasi_hari'=>3]);
        return compact('kepala','timA','timB','ketua','anggota','anggotaLain','sop','satu','dua');
    }

    private function buatInduk(array $d): Tugas
    {
        $this->actingAs($d['kepala'])->post(route('sop.delegasi.simpan', $d['sop']), [
            'nama'=>'SOP Analisis Bulanan', 'bagian_id'=>$d['timB']->id,
            'ketua_id'=>$d['ketua']->id, 'tanggal_mulai'=>'2026-10-21',
            'jenis_input'=>'surat','nomor_surat'=>'B-123/2026',
        ])->assertRedirect()->assertSessionHasNoErrors();
        return Tugas::firstOrFail();
    }

    private function disposisi(array $d, int $stepId, int $userId)
    {
        return $this->post(route('sop.delegasi.simpan-langkah', Tugas::firstOrFail()->sopPelaksanaan), [
            'sop_langkah_id'=>$stepId, 'ditugaskan_ke'=>$userId,
            'tanggal_mulai'=>'2026-10-21', 'tenggat'=>'2026-10-30',
        ]);
    }

    public function test_kepala_bps_dapat_delegasi_sop_tanpa_membuat_semua_langkah(): void
    {
        $d = $this->siapkan();
        $this->actingAs($d['kepala'])->get(route('sop.delegasi.buat',$d['sop']))
            ->assertOk()->assertSeeInOrder(['01 — Tim A', '02 — Tim B']);
        $induk = $this->buatInduk($d);
        $this->assertSame($d['ketua']->id, $induk->ditugaskan_ke);
        $this->assertSame($d['timB']->id, $induk->bagian_id);
        $this->assertSame(1, Tugas::count());
        $this->assertSame($induk->id, $induk->sopPelaksanaan->tugas_induk_id);
        $this->assertTrue($induk->adalahIndukDelegasiSop());
    }

    public function test_kepala_hanya_boleh_menunjuk_ketua_pada_tim_yang_dipilih(): void
    {
        $d = $this->siapkan();
        $this->actingAs($d['kepala'])->post(route('sop.delegasi.simpan',$d['sop']), [
            'nama'=>'Pengolahan','bagian_id'=>$d['timA']->id,
            'ketua_id'=>$d['ketua']->id,'tanggal_mulai'=>'2026-10-21','jenis_input'=>'manual',
        ])->assertSessionHasErrors('ketua_id');
        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_ketua_disposisi_setiap_langkah_dan_output_sebelumnya_jadi_input(): void
    {
        $d = $this->siapkan();
        $induk = $this->buatInduk($d);
        $this->actingAs($d['ketua'])->get(route('sop.delegasi.langkah',$induk->sopPelaksanaan))->assertForbidden();
        $this->post(route('mode-kerja.ganti'),['mode'=>'ketua'])->assertRedirect();
        $this->get(route('sop.delegasi.langkah',$induk->sopPelaksanaan))->assertOk()->assertSee('Validasi data');
        // Tahap dua belum boleh dibuat sebelum tahap satu.
        $this->disposisi($d, $d['dua']->id, $d['anggota']->id)->assertSessionHasErrors('sop_langkah_id');
        $this->disposisi($d, $d['satu']->id, $d['anggota']->id)->assertSessionHasNoErrors();
        $this->disposisi($d, $d['satu']->id, $d['anggota']->id)->assertSessionHasErrors('sop_langkah_id');
        $this->disposisi($d, $d['dua']->id, $d['anggota']->id)->assertSessionHasNoErrors();
        $anak1 = Tugas::where('sop_langkah_id',$d['satu']->id)->firstOrFail();
        $anak2 = Tugas::where('sop_langkah_id',$d['dua']->id)->firstOrFail();
        $this->assertSame($induk->id, $anak1->induk_tugas_id);
        $this->assertSame($induk->id, $anak2->induk_tugas_id);
        $this->assertSame('output_tugas', $anak2->jenis_input);
        $this->assertSame($anak1->id, $anak2->input_tugas_id);
        $this->assertSame('dikerjakan', $induk->fresh()->status);
    }

    public function test_ketua_tidak_dapat_mendisposisikan_ke_anggota_tim_lain_atau_mode_anggota(): void
    {
        $d = $this->siapkan(); $induk = $this->buatInduk($d);
        $this->actingAs($d['anggota'])->withSession(['mode_kerja'=>'ketua'])
            ->post(route('sop.delegasi.simpan-langkah',$induk->sopPelaksanaan), [])->assertForbidden();
        $this->actingAs($d['ketua'])->post(route('mode-kerja.ganti'),['mode'=>'ketua']);
        $this->disposisi($d, $d['satu']->id, $d['anggotaLain']->id)
            ->assertSessionHasErrors('ditugaskan_ke');
        $this->assertDatabaseCount('tugas', 1);
    }

    public function test_tugas_induk_tidak_selesai_sebelum_semua_langkah_dibagi_dan_selesai(): void
    {
        $d=$this->siapkan(); $induk=$this->buatInduk($d);
        $this->actingAs($d['ketua'])->post(route('mode-kerja.ganti'),['mode'=>'ketua']);
        $this->patch(route('pekerjaan.update',$induk), ['status'=>'selesai','output_realisasi'=>'Selesai'])
            ->assertSessionHasErrors('status');
        $this->disposisi($d,$d['satu']->id,$d['anggota']->id);
        $this->patch(route('pekerjaan.update',$induk), ['status'=>'selesai','output_realisasi'=>'Selesai'])
            ->assertSessionHasErrors('status');
        $this->disposisi($d,$d['dua']->id,$d['anggota']->id);
        $this->patch(route('pekerjaan.update',$induk), ['status'=>'selesai','output_realisasi'=>'Selesai'])
            ->assertSessionHasErrors('status');
        $satu = Tugas::where('sop_langkah_id',$d['satu']->id)->firstOrFail();
        $dua = Tugas::where('sop_langkah_id',$d['dua']->id)->firstOrFail();
        $this->actingAs($d['anggota'])->patch(route('pekerjaan.update',$satu), ['status'=>'selesai','output_realisasi'=>'Data valid'])->assertSessionHasNoErrors();
        $this->patch(route('pekerjaan.update',$dua), ['status'=>'selesai','output_realisasi'=>'Laporan'])->assertSessionHasNoErrors();
        $this->actingAs($d['ketua'])->patch(route('pekerjaan.update',$induk), ['status'=>'selesai','output_realisasi'=>'Seluruh output terpenuhi'])->assertSessionHasNoErrors();
        $this->assertSame('selesai',$induk->fresh()->status);
    }
}
