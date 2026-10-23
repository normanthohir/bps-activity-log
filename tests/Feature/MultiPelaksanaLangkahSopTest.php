<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiPelaksanaLangkahSopTest extends TestCase
{
    use RefreshDatabase;

    private function konteks(): array
    {
        $kepala = User::factory()->create(['role' => 'kepala_bps']);
        $tim = Bagian::create(['kode_bagian' => '01', 'nama_bagian' => 'Tim A']);
        $timLain = Bagian::create(['kode_bagian' => '02', 'nama_bagian' => 'Tim B']);
        $ketua = User::factory()->create(['role' => 'staf']);
        $a = User::factory()->create(['role' => 'staf']);
        $b = User::factory()->create(['role' => 'staf']);
        $asing = User::factory()->create(['role' => 'staf']);
        KeanggotaanTim::tetapkanKetua($tim, $ketua);
        $a->tim()->syncWithoutDetaching([$tim->id => ['peran_tim' => 'anggota']]);
        $b->tim()->syncWithoutDetaching([$tim->id => ['peran_tim' => 'anggota']]);
        $asing->tim()->syncWithoutDetaching([$timLain->id => ['peran_tim' => 'anggota']]);

        $sop = Sop::create(['kode' => 'MULTI-01', 'nama' => 'SOP Multi', 'versi' => '1', 'dibuat_oleh' => $kepala->id]);
        $satu = $sop->langkah()->create(['urutan' => 1, 'input' => 'Data', 'proses' => 'Validasi', 'output' => 'Data valid', 'sumber_input' => 'manual', 'durasi_hari' => 1]);
        $dua = $sop->langkah()->create(['urutan' => 2, 'input' => 'Data valid', 'proses' => 'Analisis', 'output' => 'Hasil analisis', 'sumber_input' => 'sebelumnya', 'durasi_hari' => 1]);
        $this->actingAs($kepala)->post(route('sop.delegasi.simpan', $sop), [
            'nama' => 'Eksekusi Multi', 'bagian_id' => $tim->id,
            'ketua_id' => $ketua->id, 'tanggal_mulai' => '2026-10-21', 'jenis_input' => 'manual',
        ])->assertSessionHasNoErrors();
        $induk = Tugas::whereNull('sop_langkah_id')->firstOrFail();
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode' => 'ketua']);
        return compact('kepala','tim','timLain','ketua','a','b','asing','sop','satu','dua','induk');
    }

    private function berikan(Tugas $induk, int $langkahId, array $ids)
    {
        return $this->post(route('sop.delegasi.simpan-langkah', $induk->sopPelaksanaan), [
            'sop_langkah_id' => $langkahId, 'penerima_ids' => $ids,
            'tanggal_mulai' => '2026-10-21', 'tenggat' => '2026-10-31',
        ]);
    }

    public function test_satu_langkah_bisa_untuk_beberapa_pegawai_termasuk_ketua_sendiri(): void
    {
        $d = $this->konteks();
        $this->get(route('sop.delegasi.langkah', $d['induk']->sopPelaksanaan))
            ->assertOk()->assertSee($d['ketua']->name)->assertSee('Saya sendiri');
        $this->berikan($d['induk'], $d['satu']->id, [$d['a']->id, $d['b']->id, $d['ketua']->id])
            ->assertSessionHasNoErrors();
        $tugas = Tugas::where('sop_langkah_id', $d['satu']->id)->get();
        $this->assertCount(3, $tugas);
        $this->assertEqualsCanonicalizing([$d['a']->id, $d['b']->id, $d['ketua']->id], $tugas->pluck('ditugaskan_ke')->all());
        $this->assertSame(1, Tugas::whereNull('sop_langkah_id')->count());
        // Dalam mode anggota, Ketua tetap dapat membuka pekerjaan yang diterimanya sendiri.
        $this->post(route('mode-kerja.ganti'), ['mode' => 'anggota']);
        $tugasKetua = $tugas->firstWhere('ditugaskan_ke', $d['ketua']->id);
        $this->get(route('pekerjaan.show', $tugasKetua))->assertOk();
        $this->patch(route('pekerjaan.update', $tugasKetua), [
            'status' => 'selesai', 'output_realisasi' => 'Hasil ketua',
        ])->assertSessionHasNoErrors();
    }

    public function test_duplikasi_dan_pegawai_luar_tim_membatalkan_seluruh_batch(): void
    {
        $d = $this->konteks();
        $this->berikan($d['induk'], $d['satu']->id, [$d['a']->id])->assertSessionHasNoErrors();
        $this->berikan($d['induk'], $d['satu']->id, [$d['a']->id, $d['b']->id])->assertSessionHasErrors('sop_langkah_id');
        $this->berikan($d['induk'], $d['satu']->id, [$d['b']->id, $d['asing']->id])->assertSessionHasErrors('penerima_ids');
        $this->berikan($d['induk'], $d['satu']->id, [$d['b']->id, $d['b']->id])->assertSessionHasErrors('penerima_ids.1');
        $this->assertEqualsCanonicalizing([$d['a']->id], Tugas::where('sop_langkah_id', $d['satu']->id)->pluck('ditugaskan_ke')->all());
        $this->berikan($d['induk'], $d['satu']->id, [$d['b']->id])->assertSessionHasNoErrors();
        $this->assertSame(2, Tugas::where('sop_langkah_id', $d['satu']->id)->count());
    }

    public function test_langkah_lanjutan_menunggu_semua_output_lalu_induk_menunggu_semua_pelaksana(): void
    {
        $d = $this->konteks();
        $this->berikan($d['induk'], $d['satu']->id, [$d['a']->id, $d['ketua']->id])->assertSessionHasNoErrors();
        $this->berikan($d['induk'], $d['dua']->id, [$d['b']->id, $d['ketua']->id])->assertSessionHasNoErrors();
        $tugas1 = Tugas::where('sop_langkah_id', $d['satu']->id)->orderBy('id')->get();
        $tugas2 = Tugas::where('sop_langkah_id', $d['dua']->id)->orderBy('id')->get();
        $this->assertCount(2, $tugas2[0]->semuaSumberOutput());
        $this->actingAs($d['b'])->patch(route('pekerjaan.update', $tugas2[0]), [
            'status' => 'dikerjakan',
        ])->assertSessionHasErrors('status');
        $this->actingAs($d['a'])->patch(route('pekerjaan.update', $tugas1[0]), [
            'status' => 'selesai', 'output_realisasi' => 'Hasil A',
        ])->assertSessionHasNoErrors();
        $this->actingAs($d['b'])->patch(route('pekerjaan.update', $tugas2[0]), [
            'status' => 'dikerjakan',
        ])->assertSessionHasErrors('status');
        $this->actingAs($d['ketua'])->patch(route('pekerjaan.update', $tugas1[1]), [
            'status' => 'selesai', 'output_realisasi' => 'Hasil Ketua',
        ])->assertSessionHasNoErrors();
        $this->actingAs($d['b'])->patch(route('pekerjaan.update', $tugas2[0]), [
            'status' => 'selesai', 'output_realisasi' => 'Hasil B',
        ])->assertSessionHasNoErrors();
        $this->actingAs($d['ketua'])->post(route('mode-kerja.ganti'), ['mode' => 'ketua']);
        $this->patch(route('pekerjaan.update', $d['induk']), [
            'status' => 'selesai', 'output_realisasi' => 'Selesai',
        ])->assertSessionHasErrors('status');
        $this->patch(route('pekerjaan.update', $tugas2[1]), [
            'status' => 'selesai', 'output_realisasi' => 'Hasil Ketua 2',
        ])->assertSessionHasNoErrors();
        $this->patch(route('pekerjaan.update', $d['induk']), [
            'status' => 'selesai', 'output_realisasi' => 'Semua selesai',
        ])->assertSessionHasNoErrors();
        $this->assertSame('selesai', $d['induk']->fresh()->status);
    }

    public function test_tidak_bisa_menambah_pelaksana_langkah_awal_setelah_langkah_berikutnya_didisposisikan(): void
    {
        $d = $this->konteks();
        $this->berikan($d['induk'], $d['satu']->id, [$d['a']->id])->assertSessionHasNoErrors();
        $this->berikan($d['induk'], $d['dua']->id, [$d['b']->id])->assertSessionHasNoErrors();
        $this->berikan($d['induk'], $d['satu']->id, [$d['ketua']->id])->assertSessionHasErrors('sop_langkah_id');
        $this->assertSame(1, Tugas::where('sop_langkah_id', $d['satu']->id)->count());
    }
}
