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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:staf'])->group(function () {
        Route::resource('laporan', LaporanController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

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
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('bagian', BagianController::class);
    });
});

require __DIR__ . '/auth.php';
