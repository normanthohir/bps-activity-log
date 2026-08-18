<?php

// use App\Http\Controllers\ProfileController;
// use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BagianController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KepalaBagian\ApprovalController as KabagApprovalController;
use App\Http\Controllers\KepalaBagian\TugasController as KabagTugasController;
use App\Http\Controllers\KepalaBPS\ApprovalController as KepalaBpsApprovalController;
use App\Http\Controllers\KepalaBPS\RekapController;
use App\Http\Controllers\Staf\LaporanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard tunggal — isi tampilannya berbeda otomatis
    // sesuai role, ditentukan di dalam DashboardController.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Laporan harian: bisa diakses SEMUA role yang login ──
    // (staf isi laporan sendiri, kepala bagian & kepala BPS juga
    // punya laporan pribadi karena mereka juga pegawai)
    Route::resource('laporan', LaporanController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    // ── Khusus Kepala Bagian ─────────────────────────────
    Route::middleware(['role:kepala_bagian'])->prefix('kabag')->name('kabag.')->group(function () {
        Route::resource('tugas', KabagTugasController::class)
            ->only(['index', 'create', 'store']);

        Route::get('approval', [KabagApprovalController::class, 'index'])->name('approval.index');
        Route::post('approval/{laporan}', [KabagApprovalController::class, 'proses'])->name('approval.proses');
    });

    // ── Khusus Kepala BPS (akses lintas bagian) ─────────
    Route::middleware(['role:kepala_bps'])->prefix('kepala-bps')->name('kepala-bps.')->group(function () {
        Route::get('rekap', [RekapController::class, 'index'])->name('rekap');

        Route::get('approval', [KepalaBpsApprovalController::class, 'index'])->name('approval.index');
        Route::post('approval/{laporan}', [KepalaBpsApprovalController::class, 'proses'])->name('approval.proses');
    });

    // ── Khusus Admin ─────────────────────────────────────
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('bagian', BagianController::class);
    });
});

require __DIR__ . '/auth.php';
