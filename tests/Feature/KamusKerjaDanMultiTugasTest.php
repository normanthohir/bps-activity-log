<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class KamusKerjaDanMultiTugasTest extends TestCase
{
    use RefreshDatabase;

    private function setupTim(): array
    {
        $ketua = User::factory()->create(['role'=>'staf']);
        $satu = User::factory()->create(['role'=>'staf']);
        $dua = User::factory()->create(['role'=>'staf']);
        $luar = User::factory()->create(['role'=>'staf']);
        $tim = Bagian::create(['nama_bagian'=>'Tim A','kode_bagian'=>'A']);
        $timB = Bagian::create(['nama_bagian'=>'Tim B','kode_bagian'=>'B']);
        KeanggotaanTim::tetapkanKetua($tim, $ketua);
        $satu->tim()->attach($tim->id);
        $dua->tim()->attach($tim->id);
        $luar->tim()->attach($timB->id);
        return [$ketua,$satu,$dua,$luar,$tim,$timB];
    }

    public function test_ketua_bisa_memberi_tugas_kepada_diri_dan_dua_pegawai_pada_tim_sama(): void
    {
        [$ketua,$satu,$dua,,$tim] = $this->setupTim();
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode'=>'ketua']);
        $this->get(route('pekerjaan.create'))->assertOk()->assertSee('(Saya sendiri)');
        $this->post(route('pekerjaan.store'), [
            'sumber_judul'=>'manual','judul'=>'Pemeriksaan Data', 'bagian_id'=>$tim->id,
            'penerima_ids'=>[$ketua->id,$satu->id,$dua->id], 'jenis_input'=>'manual',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('tugas', 3);
        $this->assertEqualsCanonicalizing([$ketua->id,$satu->id,$dua->id], Tugas::pluck('ditugaskan_ke')->all());
        $this->assertTrue(Tugas::where('ditugaskan_ke',$ketua->id)->exists());
    }

    public function test_orang_luar_tim_membatalkan_semua_penugasan_dan_duplikat_ditolak(): void
    {
        [$ketua,$satu,,$luar,$tim] = $this->setupTim();
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode'=>'ketua']);
        $this->post(route('pekerjaan.store'), [
            'judul'=>'Tidak boleh', 'bagian_id'=>$tim->id,
            'penerima_ids'=>[$satu->id,$luar->id], 'jenis_input'=>'manual',
        ])->assertSessionHasErrors('penerima_ids');
        $this->assertDatabaseCount('tugas',0);
        $this->post(route('pekerjaan.store'), [
            'judul'=>'Duplikat', 'bagian_id'=>$tim->id,
            'penerima_ids'=>[$satu->id,$satu->id], 'jenis_input'=>'manual',
        ])->assertSessionHasErrors('penerima_ids.1');
        $this->assertDatabaseCount('tugas',0);
    }

    public function test_mode_anggota_dilarang_memberi_tugas_ke_diri_sendiri(): void
    {
        [$ketua,,,,$tim] = $this->setupTim();
        $this->actingAs($ketua)->post(route('pekerjaan.store'), [
            'judul'=>'Coba', 'bagian_id'=>$tim->id, 'penerima_ids'=>[$ketua->id], 'jenis_input'=>'manual',
        ])->assertForbidden();
    }

    public function test_ketua_membuat_kamus_harus_memilih_tim_dan_bisa_memakai_input_output_kosong(): void
    {
        [$ketua,,,,$tim,$timB] = $this->setupTim();
        $this->actingAs($ketua)->post(route('mode-kerja.ganti'), ['mode'=>'ketua']);
        $this->post(route('kamus.store'), ['nama'=>'Kamus Tanpa Rincian'])->assertSessionHasErrors('bagian_id');
        $this->post(route('kamus.store'), ['nama'=>'Kamus B','bagian_id'=>$timB->id])->assertSessionHasErrors('bagian_id');
        $this->post(route('kamus.store'), ['nama'=>'Kamus A','bagian_id'=>$tim->id])
            ->assertRedirect(route('kamus.index'))->assertSessionHasNoErrors();
        $this->assertNull(KamusKerja::firstOrFail()->output);
    }

    private function fileXlsx(array $rows): UploadedFile
    {
        $buku = new Spreadsheet();
        $sheet = $buku->getActiveSheet();
        $sheet->setTitle('Kamus Kerja');
        foreach ($rows as $r => $values) foreach ($values as $c => $value) {
            $sheet->setCellValueExplicitByColumnAndRow($c+1,$r+1,(string)$value,DataType::TYPE_STRING);
        }
        $temp = tempnam(sys_get_temp_dir(), 'kamus');
        (new Xlsx($buku))->save($temp);
        $content = file_get_contents($temp);
        unlink($temp);
        $buku->disconnectWorksheets();
        return UploadedFile::fake()->createWithContent('kamus.xlsx', $content);
    }

    public function test_import_excel_boleh_hanya_nama_dan_rollback_jika_duplikat(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $this->actingAs($admin)->get(route('kamus.template-excel'))->assertOk();
        $header = ['nama','input','proses','output'];
        $this->post(route('kamus.import-excel'), ['file_excel'=>$this->fileXlsx([
            $header, ['Analisis Data','','',''], ['Validasi Data','','','']
        ])])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('kamus_kerja',2);
        $this->post(route('kamus.import-excel'), ['file_excel'=>$this->fileXlsx([
            $header, ['Baru','','',''], ['Analisis Data','','','']
        ])])->assertSessionHasErrors('file_excel');
        $this->assertDatabaseMissing('kamus_kerja',['nama'=>'Baru']);
    }
}
