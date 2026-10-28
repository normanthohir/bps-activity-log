<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurPekerjaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_dapat_disposisi_ke_ketua_tim_dan_ketua_tim_ke_staf(): void
    {
        $tim = Bagian::create(['nama_bagian'=>'Seksi A','kode_bagian'=>'A']);
        $kepala = User::factory()->create(['role'=>'kepala_bps']);
        $ketua = User::factory()->create(['role'=>'kepala_bagian','bagian_id'=>$tim->id]);
        $staf = User::factory()->create(['role'=>'staf','bagian_id'=>$tim->id]);
        \App\Support\KeanggotaanTim::tetapkanKetua($tim, $ketua);
        $staf->tim()->attach($tim->id);

        $this->actingAs($kepala)->post(route('pekerjaan.store'),[
            'bagian_id'=>$tim->id,'ditugaskan_ke'=>$ketua->id,
            'judul'=>'Rekap data','deskripsi'=>'Menyusun rekap', 'jenis_input'=>'surat',
            'uraian_input'=>'Surat provinsi','output_rencana'=>'Rekap final',
        ])->assertRedirect();
        $induk = Tugas::latest()->firstOrFail();

        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])->assertRedirect(route('dashboard'));
        $this->post(route('pekerjaan.store'),[
            'bagian_id'=>$tim->id,'ditugaskan_ke'=>$staf->id,
            'judul'=>'Input data','deskripsi'=>'Proses entry','jenis_input'=>'manual',
            'induk_tugas_id'=>$induk->id,'output_rencana'=>'Data entry',
        ])->assertRedirect();
        $anak = Tugas::latest()->firstOrFail();
        $this->assertSame($induk->id,$anak->induk_tugas_id);
        $this->actingAs($staf)->patch(route('pekerjaan.update',$anak),[
            'status'=>'selesai','output_realisasi'=>'Data entry telah dibuat',
        ])->assertRedirect();
        $this->assertSame('selesai',$anak->fresh()->status);
    }

    public function test_staf_tidak_dapat_melihat_tugas_orang_lain(): void
    {
        $tim = Bagian::create(['nama_bagian'=>'Seksi A','kode_bagian'=>'A']);
        $kepala = User::factory()->create(['role'=>'kepala_bps']);
        $staf1 = User::factory()->create(['role'=>'staf','bagian_id'=>$tim->id]);
        $staf2 = User::factory()->create(['role'=>'staf','bagian_id'=>$tim->id]);
        $tugas = Tugas::create(['dibuat_oleh'=>$kepala->id,'ditugaskan_ke'=>$staf1->id,
            'bagian_id'=>$tim->id,'judul'=>'Rahasia Tim','status'=>'belum_dikerjakan']);
        $this->actingAs($staf2)->get(route('pekerjaan.show',$tugas))->assertForbidden();
    }

    public function test_ketua_tim_dapat_memimpin_lebih_dari_satu_tim(): void
    {
        $a = Bagian::create(['nama_bagian'=>'Tim A','kode_bagian'=>'A']);
        $b = Bagian::create(['nama_bagian'=>'Tim B','kode_bagian'=>'B']);
        $ketua = User::factory()->create(['role'=>'kepala_bagian','bagian_id'=>$a->id]);
        \App\Support\KeanggotaanTim::tetapkanKetua($a, $ketua);
        \App\Support\KeanggotaanTim::tetapkanKetua($b, $ketua);
        $this->assertCount(2,$ketua->timDipimpinIds());
    }
}
