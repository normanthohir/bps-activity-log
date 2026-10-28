<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KamusKerjaSeeder extends Seeder
{
    // [nama, input, proses, output]
    private const DATA = [
        'UMUM' => [
            ['Pengelolaan surat masuk dan keluar', 'Surat dari instansi lain', 'Registrasi, disposisi, dan pengarsipan', 'Surat tercatat dan terarsip'],
            ['Rekap absensi pegawai', 'Data kehadiran harian', 'Rekapitulasi dan verifikasi', 'Rekap absensi bulanan'],
            ['Pengelolaan inventaris barang', 'Daftar barang kantor', 'Pemeriksaan dan pencatatan', 'Daftar inventaris mutakhir'],
            ['Penyusunan laporan keuangan', 'Bukti pengeluaran', 'Verifikasi dan pembukuan', 'Laporan keuangan bulanan'],
            ['Persiapan perjalanan dinas', 'Surat tugas', 'Penyusunan dokumen dan pemesanan', 'Dokumen perjalanan dinas lengkap'],
        ],
        'STAT-SOS' => [
            ['Pencacahan Susenas', 'Daftar rumah tangga sampel', 'Wawancara responden di lapangan', 'Kuesioner terisi'],
            ['Pemeriksaan kuesioner Susenas', 'Kuesioner terisi', 'Pemeriksaan kelengkapan dan konsistensi', 'Kuesioner terverifikasi'],
            ['Entri data Susenas', 'Kuesioner terverifikasi', 'Entri ke aplikasi pengolahan', 'Data Susenas terentri'],
            ['Validasi data kemiskinan', 'Data hasil entri', 'Pengecekan wajar dan outlier', 'Data kemiskinan tervalidasi'],
            ['Penyusunan tabel kesejahteraan rakyat', 'Data tervalidasi', 'Tabulasi dan penyusunan narasi', 'Tabel statistik kesejahteraan rakyat'],
        ],
        'STAT-PROD' => [
            ['Pendataan tanaman pangan', 'Daftar petani dan lahan', 'Pencacahan luas tanam dan panen', 'Data tanaman pangan'],
            ['Survei perikanan tangkap', 'Daftar nelayan', 'Wawancara dan pencatatan hasil tangkapan', 'Data produksi perikanan'],
            ['Rekap produksi hortikultura', 'Data hasil survei', 'Rekapitulasi per kecamatan', 'Rekap produksi hortikultura'],
            ['Pengumpulan harga produsen', 'Daftar produsen', 'Pencatatan harga di tingkat produsen', 'Data harga produsen'],
            ['Penyusunan tabel luas panen', 'Data pendataan', 'Tabulasi per kecamatan', 'Tabel luas panen'],
        ],
        'STAT-DIST' => [
            ['Survei harga konsumen', 'Daftar komoditas', 'Pencatatan harga di pasar tradisional', 'Data harga konsumen'],
            ['Rekap harga perdagangan besar', 'Data harga grosir', 'Rekapitulasi dan pemeriksaan', 'Rekap harga perdagangan besar'],
            ['Pendataan pelaku usaha perdagangan', 'Daftar usaha', 'Pencacahan usaha di lapangan', 'Data pelaku usaha'],
            ['Verifikasi data inflasi', 'Data harga bulanan', 'Pengecekan dan penghitungan perubahan harga', 'Data inflasi terverifikasi'],
            ['Penyusunan laporan survei harga perdesaan', 'Data survei', 'Analisis dan penyusunan laporan', 'Laporan survei harga perdesaan'],
        ],
        'IPDS' => [
            ['Pemeliharaan jaringan kantor', 'Laporan gangguan jaringan', 'Pengecekan dan perbaikan perangkat', 'Jaringan berfungsi normal'],
            ['Backup data survei', 'Data hasil survei', 'Penyalinan ke server cadangan', 'Arsip cadangan data'],
            ['Pembaruan konten website', 'Bahan publikasi terbaru', 'Penyuntingan dan unggah konten', 'Website terbarui'],
            ['Pengolahan data CAPI', 'Data kuesioner elektronik', 'Pembersihan dan penggabungan data', 'Dataset siap olah'],
            ['Pelayanan permintaan data', 'Permintaan data dari pengguna', 'Penyiapan dan pengiriman data', 'Data diterima pemohon'],
        ],
        'NWAS' => [
            ['Penghitungan PDRB triwulanan', 'Data sumber sektoral', 'Penghitungan nilai tambah', 'Angka PDRB triwulanan'],
            ['Analisis pertumbuhan ekonomi', 'Angka PDRB', 'Analisis tren dan penyebab', 'Naskah analisis pertumbuhan ekonomi'],
            ['Validasi neraca produksi', 'Data neraca', 'Pengecekan konsistensi antar sektor', 'Neraca produksi tervalidasi'],
            ['Rekap sumber data PDRB', 'Data dari instansi', 'Pengumpulan dan rekapitulasi', 'Rekap sumber data'],
            ['Penyusunan bahan publikasi', 'Hasil analisis', 'Penulisan dan penyuntingan', 'Naskah publikasi Kota Ambon Dalam Angka'],
        ],
    ];

    // Kamus umum (tidak terikat satu bagian)
    private const UMUM = [
        ['Rapat koordinasi bulanan', 'Agenda rapat', 'Pelaksanaan rapat dan penyusunan notulen', 'Notulen rapat'],
        ['Penyusunan laporan kinerja kantor', 'Capaian kegiatan tiap bagian', 'Penghimpunan dan penyusunan laporan', 'Laporan kinerja kantor'],
    ];

    public function run(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $idBagian = DB::table('bagian')->pluck('id', 'kode_bagian');
        $idKepala = DB::table('bagian')->pluck('kepala_bagian_id', 'kode_bagian');
        $sekarang = now();
        $baris = [];

        foreach (self::DATA as $kode => $daftar) {
            if (! isset($idBagian[$kode])) {
                continue;
            }

            foreach ($daftar as [$nama, $input, $proses, $output]) {
                $baris[] = [
                    'nama' => $nama, 'input' => $input, 'proses' => $proses, 'output' => $output,
                    'bagian_id' => $idBagian[$kode],
                    'dibuat_oleh' => $idKepala[$kode] ?? $admin->id,
                    'created_at' => $sekarang, 'updated_at' => $sekarang,
                ];
            }
        }

        foreach (self::UMUM as [$nama, $input, $proses, $output]) {
            $baris[] = [
                'nama' => $nama, 'input' => $input, 'proses' => $proses, 'output' => $output,
                'bagian_id' => null,
                'dibuat_oleh' => $admin->id,
                'created_at' => $sekarang, 'updated_at' => $sekarang,
            ];
        }

        DB::table('kamus_kerja')->insert($baris);
    }
}