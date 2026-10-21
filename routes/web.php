<?php

use App\Http\Controllers\Admin\BagianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KepalaBagian\ApprovalController as KabagApprovalController;
use App\Http\Controllers\KepalaBagian\TugasController as KabagTugasController;
use App\Http\Controllers\KepalaBagian\TugasAktifController;
use App\Http\Controllers\KepalaBPS\ApprovalController as KepalaBpsApprovalController;
use App\Http\Controllers\KepalaBPS\RekapController;
use App\Http\Controllers\KepalaBPS\TugasController as KepalaBpsTugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staf\LaporanController;
use App\Http\Controllers\Staf\TugasController as StafTugasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SopDelegasiController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\ModeKerjaController;
use App\Http\Controllers\KamusKerjaController;
use App\Http\Controllers\KamusExcelController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('mode-kerja', [ModeKerjaController::class, 'ganti'])->name('mode-kerja.ganti');

    // Kamus Kerja: deklarasi oleh Admin atau Ketua Tim; template & impor Excel.
    Route::get('kamus-kerja', [KamusKerjaController::class, 'index'])->name('kamus.index');
    Route::post('kamus-kerja', [KamusKerjaController::class, 'store'])->name('kamus.store');
    Route::get('kamus-kerja/template-excel', [KamusExcelController::class, 'template'])->name('kamus.template-excel');
    Route::post('kamus-kerja/import-excel', [KamusExcelController::class, 'import'])->name('kamus.import-excel');
    Route::get('kamus-kerja/{kamus}/edit', [KamusKerjaController::class, 'edit'])->name('kamus.edit');
    Route::patch('kamus-kerja/{kamus}', [KamusKerjaController::class, 'update'])->name('kamus.update');
    Route::delete('kamus-kerja/{kamus}', [KamusKerjaController::class, 'destroy'])->name('kamus.destroy');

    // Alur penugasan IPO + SOP (dipakai seluruh peran sesuai hak akses).
    Route::get('pekerjaan', [PekerjaanController::class,'index'])->name('pekerjaan.index');
    Route::get('pekerjaan/buat', [PekerjaanController::class,'create'])->name('pekerjaan.create');
    Route::post('pekerjaan', [PekerjaanController::class,'store'])->name('pekerjaan.store');
    Route::get('pekerjaan/{tugas}', [PekerjaanController::class,'show'])->name('pekerjaan.show');
    Route::patch('pekerjaan/{tugas}', [PekerjaanController::class,'update'])->name('pekerjaan.update');
    Route::get('pekerjaan/{tugas}/edit-rincian', [PekerjaanController::class,'editRincian'])->name('pekerjaan.edit-rincian');
    Route::patch('pekerjaan/{tugas}/edit-rincian', [PekerjaanController::class,'updateRincian'])->name('pekerjaan.update-rincian');
    Route::get('pekerjaan/{tugas}/berkas/{jenis}', [PekerjaanController::class,'unduh'])->name('pekerjaan.berkas');

    Route::get('sop', [SopController::class,'index'])->name('sop.index');
    Route::get('sop/template-excel', [ExcelImportController::class, 'templateSop'])->name('sop.template-excel');
    Route::post('sop/import-excel', [ExcelImportController::class, 'importSop'])->name('sop.import-excel');
    Route::get('sop/buat', [SopController::class,'create'])->name('sop.create');
    Route::post('sop', [SopController::class,'store'])->name('sop.store');
    Route::get('sop/pelaksanaan/{pelaksanaan}', [SopController::class,'pelaksanaan'])->name('sop.pelaksanaan');
    Route::post('sop/{sop}/mulai-tim', [SopDelegasiController::class,'mulaiTim'])->name('sop.mulai-tim');
    Route::get('sop/{sop}/delegasikan', [SopDelegasiController::class,'buat'])->name('sop.delegasi.buat');
    Route::post('sop/{sop}/delegasikan', [SopDelegasiController::class,'simpan'])->name('sop.delegasi.simpan');
    Route::get('sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiController::class,'langkah'])->name('sop.delegasi.langkah');
    Route::post('sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiController::class,'simpanLangkah'])->name('sop.delegasi.simpan-langkah');
    Route::get('sop/{sop}', [SopController::class,'show'])->name('sop.show');
    Route::get('sop/{sop}/edit', [SopController::class,'edit'])->name('sop.edit');
    Route::put('sop/{sop}', [SopController::class,'update'])->name('sop.update');
    Route::get('sop/{sop}/jalankan', [SopController::class,'jalankanForm'])->name('sop.jalankan-form');
    Route::post('sop/{sop}/jalankan', [SopController::class,'jalankan'])->name('sop.jalankan');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:staf'])->group(function () {
        Route::resource('laporan', LaporanController::class)
            ->only(['index', 'create', 'store', 'show', 'update', 'destroy']);

        Route::get('laporan/{laporan}/revisi', [LaporanController::class, 'edit'])->name('laporan.edit');

        Route::get('tugas-aktif', [StafTugasController::class, 'index'])->name('staf.tugas.index');
        Route::get('tugas-aktif/{tugas}', [StafTugasController::class, 'show'])->name('staf.tugas.show');
        Route::patch('tugas-aktif/{tugas}', [StafTugasController::class, 'update'])->name('staf.tugas.update');
        // ── Tugas aktif milik sendiri: bisa diakses SEMUA role yang login ──
        // (setiap pegawai bisa jadi penerima tugas, termasuk kepala bagian)
        Route::resource('tugas', StafTugasController::class)
            ->only(['index', 'show'])->parameters(['tugas' => 'tugas']);;
    });


    Route::middleware(['role:kepala_bagian'])->prefix('kabag')->name('kabag.')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'kabag'])->name('dashboard');

        Route::resource('tugas-tim', KabagTugasController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->parameters(['tugas_tim' => 'tugas'])
            ->names('tugas');

        Route::resource('tugas-aktif', TugasAktifController::class)
            ->only(['index', 'show'])
            ->parameters(['tugas_aktif' => 'tugas'])
            ->names('tugas-aktif');

        Route::resource('laporan', LaporanController::class)->names('laporan');
        Route::get('approval', [KabagApprovalController::class, 'index'])->name('approval.index');
        Route::get('approval/{laporan}', [KabagApprovalController::class, 'show'])->name('approval.show');
        Route::post('approval/{laporan}', [KabagApprovalController::class, 'proses'])->name('approval.proses');
    });

    Route::middleware(['role:kepala_bps'])->prefix('kepala-bps')->name('kepala-bps.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'kepalaBps'])->name('dashboard');

        Route::get('rekap', [RekapController::class, 'index'])->name('rekap');

        Route::resource('tugas', KepalaBpsTugasController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])->parameters(['tugas' => 'tugas']);

        Route::get('approval', [KepalaBpsApprovalController::class, 'index'])->name('approval.index');
        Route::get('approval/{laporan}', [KepalaBpsApprovalController::class, 'show'])->name('approval.show');
        Route::post('approval/{laporan}', [KepalaBpsApprovalController::class, 'proses'])->name('approval.proses');

        Route::get('rekap/export-pdf', [RekapController::class, 'exportPdf'])->name('rekap.export-pdf');
        Route::get('rekap/export-excel', [RekapController::class, 'exportExcel'])->name('rekap.export-excel');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('users/template-excel', [ExcelImportController::class, 'templateAkun'])->name('users.template-excel');
        Route::post('users/import-excel', [ExcelImportController::class, 'importAkun'])->name('users.import-excel');
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('bagian/template-excel', [ExcelImportController::class, 'templateTim'])->name('bagian.template-excel');
        Route::post('bagian/import-excel', [ExcelImportController::class, 'importTim'])->name('bagian.import-excel');
        Route::resource('bagian', BagianController::class);
    });
});

require __DIR__ . '/auth.php';
