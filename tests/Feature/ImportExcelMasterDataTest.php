<?php

namespace Tests\Feature;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportExcelMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function xlsx(array $sheets): UploadedFile
    {
        $book = new Spreadsheet();
        $first = true;
        foreach ($sheets as $sheetName => $rows) {
            $sheet = $first ? $book->getActiveSheet() : $book->createSheet();
            $first = false;
            $sheet->setTitle($sheetName);
            foreach ($rows as $y => $row) {
                foreach (array_values($row) as $x => $value) {
                    $sheet->setCellValueExplicitByColumnAndRow($x + 1, $y + 1, (string) $value, DataType::TYPE_STRING);
                }
            }
        }
        $file = tempnam(sys_get_temp_dir(), 'excel-import-');
        (new Xlsx($book))->save($file);
        $content = file_get_contents($file);
        unlink($file);
        $book->disconnectWorksheets();
        return UploadedFile::fake()->createWithContent('data.xlsx', $content);
    }

    public function test_import_akun_memelihara_nip_18_digit_dan_tim_tambahan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = Bagian::create(['kode_bagian' => 'TIM-A', 'nama_bagian' => 'Tim A']);
        $b = Bagian::create(['kode_bagian' => 'TIM-B', 'nama_bagian' => 'Tim B']);
        $this->actingAs($admin)->post(route('admin.users.import-excel'), [
            'file_excel' => $this->xlsx(['Akun' => [
                ['nama', 'nip', 'email', 'role', 'kode_tim_utama', 'nip_atasan', 'kode_tim_tambahan', 'kode_tim_dipimpin'],
                ['Ketua', '198807072010011001', 'ketua@example.test', 'staf', 'TIM-A', '', 'TIM-B', 'TIM-A'],
                ['Staf', '199909092020012002', 'staf@example.test', 'staf', 'TIM-B', '198807072010011001', 'TIM-A', ''],
            ]]),
        ])->assertSessionHasNoErrors();
        $ketua = User::where('nip', '198807072010011001')->firstOrFail();
        $staf = User::where('nip', '199909092020012002')->firstOrFail();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $ketua->anggotaTimIds());
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $staf->anggotaTimIds());
        $this->assertSame($ketua->id, $staf->atasan_id);
        $this->assertSame($ketua->id, $a->fresh()->kepala_bagian_id);
        $this->assertSame('ketua', $ketua->fresh()->tim()->where('bagian.id',$a->id)->first()->pivot->peran_tim);
        $this->assertSame('anggota', $ketua->fresh()->tim()->where('bagian.id',$b->id)->first()->pivot->peran_tim);
    }

    public function test_import_akun_batal_seluruhnya_bila_ada_duplikat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tim = Bagian::create(['kode_bagian' => 'A', 'nama_bagian' => 'A']);
        $this->actingAs($admin)->post(route('admin.users.import-excel'), [
            'file_excel' => $this->xlsx(['Akun' => [
                ['nama', 'nip', 'email', 'role', 'kode_tim_utama', 'nip_atasan', 'kode_tim_tambahan', 'kode_tim_dipimpin'],
                ['A', '12345', 'a@example.test', 'staf', 'A', '', '', ''],
                ['B', '12345', 'b@example.test', 'staf', 'A', '', '', ''],
            ]]),
        ])->assertSessionHasErrors('file_excel');
        $this->assertDatabaseMissing('users', ['nip' => '12345']);
    }

    public function test_import_tim_mendukung_satu_ketua_memimpin_dua_tim(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ketua = User::factory()->create(['role' => 'staf', 'nip' => '198001012006011001']);
        $staf = User::factory()->create(['role' => 'staf', 'nip' => '199202022018012002']);
        $this->actingAs($admin)->post(route('admin.bagian.import-excel'), [
            'file_excel' => $this->xlsx([
                'Tim' => [
                    ['kode_tim', 'nama_tim', 'nip_ketua'],
                    ['A', 'Tim A', $ketua->nip],
                    ['B', 'Tim B', $ketua->nip],
                ],
                'Anggota Tim' => [
                    ['kode_tim', 'nip_pegawai'],
                    ['A', $staf->nip],
                    ['B', $staf->nip],
                ],
            ]),
        ])->assertSessionHasNoErrors();
        $this->assertCount(2, $ketua->fresh()->timDipimpinIds());
        $this->assertCount(2, $staf->fresh()->anggotaTimIds());
        $timA = Bagian::where('kode_bagian', 'A')->firstOrFail();
        $this->assertSame('anggota', $staf->fresh()->tim()->where('bagian.id', $timA->id)->first()->pivot->peran_tim);
    }

    public function test_import_sop_membuat_langkah_ipo_berurutan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('sop.import-excel'), [
            'file_excel' => $this->xlsx(['SOP' => [
                ['kode_sop', 'nama_sop', 'versi', 'keterangan', 'urutan', 'input', 'proses', 'output', 'sumber_input', 'durasi_hari'],
                ['SOP-01', 'Rekap', '1.0', 'SOP uji', '2', 'Data terverifikasi', 'Rekapitulasi', 'Laporan', 'sebelumnya', '3'],
                ['SOP-01', 'Rekap', '1.0', 'SOP uji', '1', 'Surat', 'Verifikasi', 'Data terverifikasi', 'surat', '2'],
            ]]),
        ])->assertSessionHasNoErrors();
        $sop = Sop::where('kode', 'SOP-01')->firstOrFail();
        $this->assertSame(['Verifikasi', 'Rekapitulasi'], $sop->langkah()->pluck('proses')->all());
    }

    public function test_staf_tidak_bisa_import_master_data(): void
    {
        $staf = User::factory()->create(['role' => 'staf']);
        $this->actingAs($staf)->get(route('admin.bagian.template-excel'))->assertForbidden();
        $this->actingAs($staf)->get(route('sop.template-excel'))->assertForbidden();
    }
}
