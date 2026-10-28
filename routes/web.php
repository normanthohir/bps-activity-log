<?php

use App\Http\Controllers\Admin\BagianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\KamusExcelController;
use App\Http\Controllers\KamusKerjaController;
use App\Http\Controllers\KepalaBagian\ApprovalController as KabagApprovalController;
use App\Http\Controllers\KepalaBagian\TugasAktifController;
use App\Http\Controllers\KepalaBagian\TugasController as KabagTugasController;
use App\Http\Controllers\KepalaBPS\ApprovalController as KepalaBpsApprovalController;
use App\Http\Controllers\KepalaBPS\RekapController;
use App\Http\Controllers\KepalaBPS\TugasController as KepalaBpsTugasController;
use App\Http\Controllers\ModeKerjaController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SopDelegasiController;
use App\Http\Controllers\Staf\LaporanController;
use App\Http\Controllers\Staf\TugasController as StafTugasController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | Dipakai semua peran (hak akses ditentukan di controller / AksesPekerjaan)
    |----------------------------------------------------------------------
    */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('mode-kerja', [ModeKerjaController::class, 'ganti'])->name('mode-kerja.ganti');

    // Profil: satu alamat untuk semua peran
    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kamus Kerja
    Route::get('kamus-kerja', [KamusKerjaController::class, 'index'])->name('kamus.index');
    Route::post('kamus-kerja', [KamusKerjaController::class, 'store'])->name('kamus.store');
    Route::get('kamus-kerja/template-excel', [KamusExcelController::class, 'template'])->name('kamus.template-excel');
    Route::post('kamus-kerja/import-excel', [KamusExcelController::class, 'import'])->name('kamus.import-excel');
    Route::get('kamus-kerja/{kamus}/edit', [KamusKerjaController::class, 'edit'])->name('kamus.edit');
    Route::patch('kamus-kerja/{kamus}', [KamusKerjaController::class, 'update'])->name('kamus.update');
    Route::delete('kamus-kerja/{kamus}', [KamusKerjaController::class, 'destroy'])->name('kamus.destroy');

    // Alur penugasan IPO
    Route::get('pekerjaan', [PekerjaanController::class, 'index'])->name('pekerjaan.index');
    Route::get('pekerjaan/buat', [PekerjaanController::class, 'create'])->name('pekerjaan.create');
    Route::post('pekerjaan', [PekerjaanController::class, 'store'])->name('pekerjaan.store');
    Route::get('pekerjaan/{tugas}', [PekerjaanController::class, 'show'])->name('pekerjaan.show');
    Route::patch('pekerjaan/{tugas}', [PekerjaanController::class, 'update'])->name('pekerjaan.update');
    Route::get('pekerjaan/{tugas}/edit-rincian', [PekerjaanController::class, 'editRincian'])->name('pekerjaan.edit-rincian');
    Route::patch('pekerjaan/{tugas}/edit-rincian', [PekerjaanController::class, 'updateRincian'])->name('pekerjaan.update-rincian');
    Route::get('pekerjaan/{tugas}/berkas/{jenis}', [PekerjaanController::class, 'unduh'])->name('pekerjaan.berkas');

    // SOP
    Route::get('sop', [SopController::class, 'index'])->name('sop.index');
    Route::get('sop/template-excel', [ExcelImportController::class, 'templateSop'])->name('sop.template-excel');
    Route::post('sop/import-excel', [ExcelImportController::class, 'importSop'])->name('sop.import-excel');
    Route::get('sop/buat', [SopController::class, 'create'])->name('sop.create');
    Route::post('sop', [SopController::class, 'store'])->name('sop.store');
    Route::get('sop/pelaksanaan/{pelaksanaan}', [SopController::class, 'pelaksanaan'])->name('sop.pelaksanaan');
    Route::post('sop/{sop}/mulai-tim', [SopDelegasiController::class, 'mulaiTim'])->name('sop.mulai-tim');
    Route::get('sop/{sop}/delegasikan', [SopDelegasiController::class, 'buat'])->name('sop.delegasi.buat');
    Route::post('sop/{sop}/delegasikan', [SopDelegasiController::class, 'simpan'])->name('sop.delegasi.simpan');
    Route::get('sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiController::class, 'langkah'])->name('sop.delegasi.langkah');
    Route::post('sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiController::class, 'simpanLangkah'])->name('sop.delegasi.simpan-langkah');
    Route::get('sop/{sop}', [SopController::class, 'show'])->name('sop.show');
    Route::get('sop/{sop}/edit', [SopController::class, 'edit'])->name('sop.edit');
    Route::put('sop/{sop}', [SopController::class, 'update'])->name('sop.update');
    Route::get('sop/{sop}/jalankan', [SopController::class, 'jalankanForm'])->name('sop.jalankan-form');
    Route::post('sop/{sop}/jalankan', [SopController::class, 'jalankan'])->name('sop.jalankan');

    /*
    |----------------------------------------------------------------------
    | Mode ANGGOTA (staf, atau kepala tim yang sedang di mode anggota)
    |----------------------------------------------------------------------
    */
    Route::middleware('mode:anggota')->group(function () {
        Route::get('laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
        Route::get('laporan/{laporan}/revisi', [LaporanController::class, 'edit'])->name('laporan.edit');
        Route::resource('laporan', LaporanController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('tugas-saya', [StafTugasController::class, 'index'])->name('staf.tugas.index');
        Route::get('tugas-saya/{tugas}', [StafTugasController::class, 'show'])->name('staf.tugas.show');
        Route::patch('tugas-saya/{tugas}', [StafTugasController::class, 'update'])->name('staf.tugas.update');

        // Peninggalan versi lama (tugas.index / tugas.show).
        // Hapus kalau pencarian  grep -rn "route('tugas\." app resources  tidak menemukan apa pun.
        Route::resource('tugas', StafTugasController::class)
            ->only(['index', 'show'])
            ->parameters(['tugas' => 'tugas']);
    });

    /*
    |----------------------------------------------------------------------
    | Mode KETUA TIM  (nama route tetap berawalan "kabag.", alamat tanpa nama role)
    |----------------------------------------------------------------------
    */
    Route::middleware('mode:ketua')->name('kabag.')->group(function () {
        // Route::get('dashboard/tim', [DashboardController::class, 'kabag'])->name('dashboard');

        // Kunci parameters harus sama persis dengan nama resource (memakai tanda hubung)
        Route::resource('tugas-tim', KabagTugasController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->parameters(['tugas-tim' => 'tugas'])
            ->names('tugas');

        Route::resource('tugas-aktif', TugasAktifController::class)
            ->only(['index', 'show'])
            ->parameters(['tugas-aktif' => 'tugas'])
            ->names('tugas-aktif');

        Route::resource('laporan-tim', LaporanController::class)
            ->only(['index', 'create', 'store', 'show', 'update', 'destroy'])
            ->parameters(['laporan-tim' => 'laporan'])
            ->names('laporan');
        Route::get('laporan-tim/{laporan}/revisi', [LaporanController::class, 'edit'])->name('laporan.edit');
        Route::get('persetujuan-tim', [KabagApprovalController::class, 'index'])->name('approval.index');
        Route::get('persetujuan-tim/{laporan}', [KabagApprovalController::class, 'show'])->name('approval.show');
        Route::post('persetujuan-tim/{laporan}', [KabagApprovalController::class, 'proses'])->name('approval.proses');
    });

    /*
    |----------------------------------------------------------------------
    | KEPALA BPS
    |----------------------------------------------------------------------
    */
    Route::middleware('role:kepala_bps')->name('kepala-bps.')->group(function () {
        // Route::get('kepala-bps/dashboard', [DashboardController::class, 'kepalaBps'])->name('dashboard');

        Route::get('rekap', [RekapController::class, 'index'])->name('rekap');
        Route::get('rekap/export-pdf', [RekapController::class, 'exportPdf'])->name('rekap.export-pdf');
        Route::get('rekap/export-excel', [RekapController::class, 'exportExcel'])->name('rekap.export-excel');

        Route::resource('penugasan', KepalaBpsTugasController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->parameters(['penugasan' => 'tugas'])
            ->names('tugas');

        Route::get('persetujuan', [KepalaBpsApprovalController::class, 'index'])->name('approval.index');
        Route::get('persetujuan/{laporan}', [KepalaBpsApprovalController::class, 'show'])->name('approval.show');
        Route::post('persetujuan/{laporan}', [KepalaBpsApprovalController::class, 'proses'])->name('approval.proses');
    });

    /*
    |----------------------------------------------------------------------
    | ADMIN
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->name('admin.')->group(function () {
        // Rute tetap ("template-excel") harus di atas resource supaya tidak ditangkap {akun}
        Route::get('akun/template-excel', [ExcelImportController::class, 'templateAkun'])->name('users.template-excel');
        Route::post('akun/import-excel', [ExcelImportController::class, 'importAkun'])->name('users.import-excel');
        Route::post('akun/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('akun/{user}/kirim-ulang-verifikasi', [UserController::class, 'resendVerification'])->name('users.resend-verification');
        Route::resource('akun', UserController::class)
            ->parameters(['akun' => 'user'])
            ->names('users');
        Route::get('tim/template-excel', [ExcelImportController::class, 'templateTim'])->name('bagian.template-excel');
        Route::post('tim/import-excel', [ExcelImportController::class, 'importTim'])->name('bagian.import-excel');
        Route::resource('tim', BagianController::class)
            ->parameters(['tim' => 'bagian'])
            ->names('bagian');
    });
});

require __DIR__ . '/auth.php';
