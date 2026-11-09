<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardApiController;
use App\Http\Controllers\API\KalenderApiController;
use App\Http\Controllers\API\MenuApiController;
use App\Http\Controllers\API\PekerjaanApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Flutter) — mencerminkan routes/web.php
|--------------------------------------------------------------------------
| Prefix otomatis: /api
| Nama route berawalan "api."
|
| Perbedaan dari web:
|  - Tidak ada halaman form (create/edit). Diganti endpoint data:
|      create -> GET .../referensi   (data pilihan form)
|      edit   -> GET .../{x}/edit-... atau GET .../{x}
|  - Mode Ketua/Anggota dibaca dari header "X-Mode-Kerja" (bukan session).
|  - Semua respons JSON. Kirim header "Accept: application/json".
|
| FASE 1 (aktif)         : auth, menu, mode, dashboard, pekerjaan
| FASE 2-5 (dikomentari) : aktifkan blok-nya setelah controller dibuat.
|--------------------------------------------------------------------------
*/

// ── Publik ────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

// ── Terproteksi token ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'verified'])->name('api.')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Dipakai semua peran (hak akses ditentukan di controller / AksesPekerjaan)
    |----------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/menu', [MenuApiController::class, 'index'])->name('menu');

    Route::get('/kalender', [KalenderApiController::class, 'index'])->name('kalender');
    // Dashboard: jenis otomatis (anggota | ketua | kepala_bps | admin)
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tim', [DashboardApiController::class, 'tim'])->name('dashboard.tim');
    Route::get('/dashboard/kantor', [DashboardApiController::class, 'kantor'])->name('dashboard.kantor');

    // nugasan IPO (padanan web: pekerjaan.*)
    // Rute statis ("referensi") wajib di atas rute berparameter {tugas}.
    Route::get('/pekerjaan/referensi', [PekerjaanApiController::class, 'create'])->name('pekerjaan.create');
    Route::get('/pekerjaan', [PekerjaanApiController::class, 'index'])->name('pekerjaan.index');
    Route::post('/pekerjaan', [PekerjaanApiController::class, 'store'])->name('pekerjaan.store');
    Route::get('/pekerjaan/{tugas}/edit-rincian', [PekerjaanApiController::class, 'editRincian'])->name('pekerjaan.edit-rincian');
    Route::patch('/pekerjaan/{tugas}/edit-rincian', [PekerjaanApiController::class, 'updateRincian'])->name('pekerjaan.update-rincian');
    Route::get('/pekerjaan/{tugas}/berkas/{jenis}', [PekerjaanApiController::class, 'unduh'])->name('pekerjaan.berkas');
    Route::get('/pekerjaan/{tugas}', [PekerjaanApiController::class, 'show'])->name('pekerjaan.show');
    Route::patch('/pekerjaan/{tugas}', [PekerjaanApiController::class, 'update'])->name('pekerjaan.update');

    /*
    |======================================================================
    | FASE 2 — Mode kerja, profil, kamus kerja, SOP
    | Buat controller lalu hapus tanda komentar.
    |======================================================================
    */

    // // Mode kerja: stateless. Endpoint ini hanya MEMVALIDASI bahwa pengguna boleh
    // // memakai mode yang diminta (body: {"mode":"ketua"|"anggota"}) dan mengembalikan
    // // menu + dashboard untuk mode itu. Penyimpanan mode ada di Flutter (header).
    // Route::post('/mode-kerja', [ModeKerjaApiController::class, 'ganti'])->name('mode-kerja.ganti');

    // // Profil
    // Route::get('/profil', [ProfilApiController::class, 'show'])->name('profile.show');
    // Route::patch('/profil', [ProfilApiController::class, 'update'])->name('profile.update');
    // Route::put('/profil/password', [ProfilApiController::class, 'password'])->name('profile.password');

    // // Kamus Kerja (rute statis di atas {kamus})
    // Route::get('/kamus-kerja', [KamusKerjaApiController::class, 'index'])->name('kamus.index');
    // Route::post('/kamus-kerja', [KamusKerjaApiController::class, 'store'])->name('kamus.store');
    // Route::get('/kamus-kerja/template-excel', [KamusExcelApiController::class, 'template'])->name('kamus.template-excel');
    // Route::post('/kamus-kerja/import-excel', [KamusExcelApiController::class, 'import'])->name('kamus.import-excel');
    // Route::get('/kamus-kerja/{kamus}', [KamusKerjaApiController::class, 'show'])->name('kamus.show');
    // Route::patch('/kamus-kerja/{kamus}', [KamusKerjaApiController::class, 'update'])->name('kamus.update');
    // Route::delete('/kamus-kerja/{kamus}', [KamusKerjaApiController::class, 'destroy'])->name('kamus.destroy');

    // // SOP (rute statis dan yang lebih panjang di atas {sop})
    // Route::get('/sop', [SopApiController::class, 'index'])->name('sop.index');
    // Route::get('/sop/template-excel', [ExcelImportApiController::class, 'templateSop'])->name('sop.template-excel');
    // Route::post('/sop/import-excel', [ExcelImportApiController::class, 'importSop'])->name('sop.import-excel');
    // Route::post('/sop', [SopApiController::class, 'store'])->name('sop.store');
    // Route::get('/sop/pelaksanaan/{pelaksanaan}', [SopApiController::class, 'pelaksanaan'])->name('sop.pelaksanaan');
    // Route::get('/sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiApiController::class, 'langkah'])->name('sop.delegasi.langkah');
    // Route::post('/sop/pelaksanaan/{pelaksanaan}/disposisi-langkah', [SopDelegasiApiController::class, 'simpanLangkah'])->name('sop.delegasi.simpan-langkah');
    // Route::post('/sop/{sop}/mulai-tim', [SopDelegasiApiController::class, 'mulaiTim'])->name('sop.mulai-tim');
    // Route::get('/sop/{sop}/delegasikan', [SopDelegasiApiController::class, 'buat'])->name('sop.delegasi.buat');      // data form
    // Route::post('/sop/{sop}/delegasikan', [SopDelegasiApiController::class, 'simpan'])->name('sop.delegasi.simpan');
    // Route::get('/sop/{sop}/jalankan', [SopApiController::class, 'jalankanForm'])->name('sop.jalankan-form');         // data form
    // Route::post('/sop/{sop}/jalankan', [SopApiController::class, 'jalankan'])->name('sop.jalankan');
    // Route::get('/sop/{sop}', [SopApiController::class, 'show'])->name('sop.show');
    // Route::put('/sop/{sop}', [SopApiController::class, 'update'])->name('sop.update');

    /*
    |======================================================================
    | FASE 3 — Mode ANGGOTA: laporan harian
    | Middleware "mode:anggota" harus membalas 403 JSON (bukan redirect).
    |======================================================================
    */
    // Route::middleware('mode:anggota')->group(function () {
    //     Route::get('/laporan/referensi', [LaporanApiController::class, 'create'])->name('laporan.create');     // data form
    //     Route::get('/laporan', [LaporanApiController::class, 'index'])->name('laporan.index');
    //     Route::post('/laporan', [LaporanApiController::class, 'store'])->name('laporan.store');
    //     Route::get('/laporan/{laporan}/revisi', [LaporanApiController::class, 'edit'])->name('laporan.edit');   // data form
    //     Route::get('/laporan/{laporan}', [LaporanApiController::class, 'show'])->name('laporan.show');
    //     Route::patch('/laporan/{laporan}', [LaporanApiController::class, 'update'])->name('laporan.update');
    //     Route::delete('/laporan/{laporan}', [LaporanApiController::class, 'destroy'])->name('laporan.destroy');
    // });
    //
    // Catatan: "tugas-saya" (staf.tugas.*) di web tidak dibuat ulang. Fungsinya sudah
    // dicakup /pekerjaan karena AksesPekerjaan::lingkup() membatasi staf ke tugasnya.

    /*
    |======================================================================
    | FASE 4 — Mode KETUA TIM (nama route berawalan "kabag.")
    |======================================================================
    */
    // Route::middleware('mode:ketua')->name('kabag.')->group(function () {
    //     Route::get('/laporan-tim/referensi', [LaporanApiController::class, 'create'])->name('laporan.create');
    //     Route::get('/laporan-tim', [LaporanApiController::class, 'index'])->name('laporan.index');
    //     Route::post('/laporan-tim', [LaporanApiController::class, 'store'])->name('laporan.store');
    //     Route::get('/laporan-tim/{laporan}/revisi', [LaporanApiController::class, 'edit'])->name('laporan.edit');
    //     Route::get('/laporan-tim/{laporan}', [LaporanApiController::class, 'show'])->name('laporan.show');
    //     Route::patch('/laporan-tim/{laporan}', [LaporanApiController::class, 'update'])->name('laporan.update');
    //     Route::delete('/laporan-tim/{laporan}', [LaporanApiController::class, 'destroy'])->name('laporan.destroy');
    //
    //     Route::get('/persetujuan-tim', [ApprovalApiController::class, 'index'])->name('approval.index');
    //     Route::get('/persetujuan-tim/{laporan}', [ApprovalApiController::class, 'show'])->name('approval.show');
    //     Route::post('/persetujuan-tim/{laporan}', [ApprovalApiController::class, 'proses'])->name('approval.proses');
    // });

    /*
    |======================================================================
    | FASE 4 — KEPALA BPS
    |======================================================================
    */
    // Route::middleware('role:kepala_bps')->name('kepala-bps.')->group(function () {
    //     Route::get('/rekap-kantor/laporan', [RekapApiController::class, 'index'])->name('rekap');
    //     Route::get('/rekap-kantor/laporan/export-pdf', [RekapApiController::class, 'exportPdf'])->name('rekap.export-pdf');
    //     Route::get('/rekap-kantor/laporan/export-excel', [RekapApiController::class, 'exportExcel'])->name('rekap.export-excel');
    //
    //     Route::get('/rekap-kantor/pekerjaan', [RekapApiController::class, 'pekerjaan'])->name('rekap.pekerjaan');
    //     Route::get('/rekap-kantor/pekerjaan/export-pdf', [RekapApiController::class, 'pekerjaanPdf'])->name('rekap.pekerjaan.export-pdf');
    //     Route::get('/rekap-kantor/pekerjaan/export-excel', [RekapApiController::class, 'pekerjaanExcel'])->name('rekap.pekerjaan.export-excel');
    //
    //     Route::get('/persetujuan-kantor', [ApprovalApiController::class, 'index'])->name('approval.index');
    //     Route::get('/persetujuan-kantor/{laporan}', [ApprovalApiController::class, 'show'])->name('approval.show');
    //     Route::post('/persetujuan-kantor/{laporan}', [ApprovalApiController::class, 'proses'])->name('approval.proses');
    // });

    /*
    |======================================================================
    | FASE 5 — ADMIN (opsional di mobile; web sudah cukup)
    |======================================================================
    */
    // Route::middleware('role:admin')->name('admin.')->group(function () {
    //     Route::get('/akun/template-excel', [ExcelImportApiController::class, 'templateAkun'])->name('users.template-excel');
    //     Route::post('/akun/import-excel', [ExcelImportApiController::class, 'importAkun'])->name('users.import-excel');
    //     Route::post('/akun/{user}/reset-password', [UserApiController::class, 'resetPassword'])->name('users.reset-password');
    //     Route::post('/akun/{user}/kirim-ulang-verifikasi', [UserApiController::class, 'resendVerification'])->name('users.resend-verification');
    //     Route::apiResource('akun', UserApiController::class)->parameters(['akun' => 'user'])->names('users');
    //
    //     Route::get('/tim/template-excel', [ExcelImportApiController::class, 'templateTim'])->name('bagian.template-excel');
    //     Route::post('/tim/import-excel', [ExcelImportApiController::class, 'importTim'])->name('bagian.import-excel');
    //     Route::apiResource('tim', BagianApiController::class)->parameters(['tim' => 'bagian'])->names('bagian');
    // });
});