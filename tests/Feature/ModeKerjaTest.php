<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModeKerjaTest extends TestCase
{
    use RefreshDatabase;

    public function test_satu_akun_bisa_beralih_mode_dan_dashboard_pekerjaan_mengikuti_hak_aktif(): void
    {
        $a = Bagian::create(['kode_bagian'=>'A', 'nama_bagian'=>'Tim A']);
        $b = Bagian::create(['kode_bagian'=>'B', 'nama_bagian'=>'Tim B']);
        $c = Bagian::create(['kode_bagian'=>'C', 'nama_bagian'=>'Tim C']);
        $kepala = User::factory()->create(['role'=>'kepala_bps']);
        $pegawai = User::factory()->create(['role'=>'staf','bagian_id'=>$b->id]);
        $stafA = User::factory()->create(['role'=>'staf']);
        $stafB = User::factory()->create(['role'=>'staf']);
        KeanggotaanTim::tetapkanKetua($a, $pegawai);
        KeanggotaanTim::tetapkanKetua($c, $pegawai);
        $pegawai->tim()->syncWithoutDetaching([$b->id => ['peran_tim'=>'anggota']]);
        $stafA->tim()->syncWithoutDetaching([$a->id => ['peran_tim'=>'anggota']]);
        $stafB->tim()->syncWithoutDetaching([$b->id => ['peran_tim'=>'anggota']]);
        $tugasA = Tugas::create(['dibuat_oleh'=>$kepala->id,'ditugaskan_ke'=>$stafA->id,'bagian_id'=>$a->id,'judul'=>'A','status'=>'belum_dikerjakan']);
        $tugasB = Tugas::create(['dibuat_oleh'=>$kepala->id,'ditugaskan_ke'=>$stafB->id,'bagian_id'=>$b->id,'judul'=>'B','status'=>'belum_dikerjakan']);
        $tugasSaya = Tugas::create(['dibuat_oleh'=>$kepala->id,'ditugaskan_ke'=>$pegawai->id,'bagian_id'=>$b->id,'judul'=>'Saya','status'=>'belum_dikerjakan']);

        $this->actingAs($pegawai)->get(route('dashboard'))->assertOk();
        $this->get(route('kabag.dashboard'))->assertForbidden();
        $this->get(route('pekerjaan.show',$tugasA))->assertForbidden();
        $this->get(route('pekerjaan.show',$tugasSaya))->assertOk();
        $this->post(route('pekerjaan.store'), [])->assertForbidden();

        $this->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('mode_kerja', 'ketua');
        $this->get(route('dashboard'))->assertRedirect(route('kabag.dashboard'));
        $this->get(route('kabag.dashboard'))->assertOk();
        $this->get(route('pekerjaan.show',$tugasA))->assertOk();
        $this->get(route('pekerjaan.show',$tugasB))->assertForbidden();
        $this->get(route('pekerjaan.show',$tugasSaya))->assertOk();

        $this->post(route('mode-kerja.ganti'), ['mode'=>'anggota'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('mode_kerja', 'anggota');
        $this->get(route('kabag.dashboard'))->assertForbidden();
        $this->get(route('pekerjaan.show',$tugasA))->assertForbidden();
    }

    public function test_pegawai_tanpa_kepemimpinan_tidak_dapat_memilih_mode_ketua(): void
    {
        $pegawai = User::factory()->create(['role'=>'staf']);
        $this->actingAs($pegawai)->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])->assertForbidden();
        $this->post(route('mode-kerja.ganti'), ['mode'=>'admin'])->assertSessionHasErrors('mode');
        $this->get(route('kabag.dashboard'))->assertForbidden();
    }

    public function test_mode_ketua_otomatis_tidak_berlaku_saat_hak_ketua_dicabut(): void
    {
        $tim = Bagian::create(['kode_bagian'=>'A','nama_bagian'=>'Tim A']);
        $pegawai = User::factory()->create(['role'=>'staf']);
        KeanggotaanTim::tetapkanKetua($tim, $pegawai);
        $this->actingAs($pegawai)->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])->assertRedirect();
        $this->get(route('kabag.dashboard'))->assertOk();
        KeanggotaanTim::tetapkanKetua($tim, null);
        $this->get(route('kabag.dashboard'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_akun_pegawai_baru_tidak_perlu_mengisi_role(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $tim = Bagian::create(['kode_bagian'=>'A','nama_bagian'=>'Tim A']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name'=>'Pegawai Baru', 'nip'=>'198702102014011234', 'email'=>'baru@example.org',
            'ketua_tim_ids'=>[$tim->id],
        ])->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();
        $baru = User::where('nip','198702102014011234')->firstOrFail();
        $this->assertSame('staf', $baru->role);
        $this->assertSame([$tim->id], $baru->timDipimpinIds());
    }

    public function test_kepala_bps_admin_tidak_bisa_beralih_ke_mode_pegawai(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $this->actingAs($admin)->post(route('mode-kerja.ganti'), ['mode'=>'ketua'])->assertForbidden();
        $kepala = User::factory()->create(['role'=>'kepala_bps']);
        $this->actingAs($kepala)->post(route('mode-kerja.ganti'), ['mode'=>'anggota'])->assertForbidden();
    }
}
