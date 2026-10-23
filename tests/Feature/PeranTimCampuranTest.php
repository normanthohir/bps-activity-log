<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use App\Support\AksesPekerjaan;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeranTimCampuranTest extends TestCase
{
    use RefreshDatabase;

    public function test_pegawai_staf_bisa_ketua_di_dua_tim_dan_anggota_di_tim_lain(): void
    {
        $a = Bagian::create(['kode_bagian'=>'A','nama_bagian'=>'Tim A']);
        $b = Bagian::create(['kode_bagian'=>'B','nama_bagian'=>'Tim B']);
        $c = Bagian::create(['kode_bagian'=>'C','nama_bagian'=>'Tim C']);
        $pemimpin = User::factory()->create(['role'=>'staf','bagian_id'=>$b->id]);
        $anggotaA = User::factory()->create(['role'=>'staf']);
        $anggotaB = User::factory()->create(['role'=>'staf']);
        $pemimpinB = User::factory()->create(['role'=>'staf']);
        KeanggotaanTim::tetapkanKetua($a, $pemimpin);
        KeanggotaanTim::tetapkanKetua($c, $pemimpin);
        KeanggotaanTim::tetapkanKetua($b, $pemimpinB);
        $pemimpin->tim()->syncWithoutDetaching([$b->id => ['peran_tim'=>'anggota']]);
        $anggotaA->tim()->attach($a->id, ['peran_tim'=>'anggota']);
        $anggotaB->tim()->attach($b->id, ['peran_tim'=>'anggota']);

        $this->assertTrue($pemimpin->isKepalaBagian());
        $this->assertTrue($pemimpin->isStaf());
        $this->assertEqualsCanonicalizing([$a->id, $c->id], $pemimpin->timDipimpinIds());
        $this->assertCount(3, $pemimpin->anggotaTimIds());
        $this->actingAs($pemimpin)->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])->assertRedirect(route('dashboard'));
        $this->assertTrue(AksesPekerjaan::tujuanValid($pemimpin, $anggotaA, $a));
        $this->assertFalse(AksesPekerjaan::tujuanValid($pemimpin, $anggotaB, $b));

        $kepala = User::factory()->create(['role'=>'kepala_bps']);
        $tugasA = Tugas::create(['dibuat_oleh'=>$kepala->id, 'ditugaskan_ke'=>$anggotaA->id,
            'bagian_id'=>$a->id, 'judul'=>'Pekerjaan A','status'=>'belum_dikerjakan']);
        $tugasB = Tugas::create(['dibuat_oleh'=>$kepala->id, 'ditugaskan_ke'=>$anggotaB->id,
            'bagian_id'=>$b->id, 'judul'=>'Pekerjaan B','status'=>'belum_dikerjakan']);
        $this->assertTrue(AksesPekerjaan::dapatLihat($pemimpin, $tugasA));
        $this->assertFalse(AksesPekerjaan::dapatLihat($pemimpin, $tugasB));
        $this->get(route('kabag.dashboard'))->assertOk();
    }

    public function test_edit_akun_memungkinkan_peran_campuran_dan_menolak_mengambil_ketua_tim_orang_lain(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $pegawai = User::factory()->create(['role'=>'staf', 'nip'=>'198607072009021001']);
        $lain = User::factory()->create(['role'=>'staf']);
        $a = Bagian::create(['kode_bagian'=>'A','nama_bagian'=>'Tim A']);
        $b = Bagian::create(['kode_bagian'=>'B','nama_bagian'=>'Tim B']);
        $c = Bagian::create(['kode_bagian'=>'C','nama_bagian'=>'Tim C']);
        KeanggotaanTim::tetapkanKetua($c, $lain);
        $payload = [
            'name'=>$pegawai->name, 'nip'=>$pegawai->nip, 'email'=>$pegawai->email,
            'role'=>'staf','bagian_ids'=>[$b->id], 'ketua_tim_ids'=>[$a->id],
        ];
        $this->actingAs($admin)->put(route('admin.users.update', $pegawai), $payload)->assertSessionHasNoErrors();
        $this->assertSame([$a->id], $pegawai->fresh()->timDipimpinIds());
        $this->assertEqualsCanonicalizing([$a->id,$b->id], $pegawai->fresh()->anggotaTimIds());
        $this->actingAs($admin)->put(route('admin.users.update', $pegawai), [
            ...$payload, 'ketua_tim_ids'=>[$a->id, $c->id],
        ])->assertSessionHasErrors('ketua_tim_ids');
        $this->assertSame($lain->id, $c->fresh()->kepala_bagian_id);
        $this->assertSame([$a->id], $pegawai->fresh()->timDipimpinIds());
    }
}
