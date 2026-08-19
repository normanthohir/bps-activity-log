<?php

use App\Http\Controllers\Admin\BagianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KepalaBagian\ApprovalController as KabagApprovalController;
use App\Http\Controllers\KepalaBagian\TugasController as KabagTugasController;
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

    Route::resource('laporan', LaporanController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::get('tugas-saya', [StafTugasController::class, 'index'])->name('staf.tugas.index');
    Route::get('tugas-saya/{tugas}', [StafTugasController::class, 'show'])->name('staf.tugas.show');
    Route::patch('tugas-saya/{tugas}', [StafTugasController::class, 'update'])->name('staf.tugas.update');

    Route::middleware(['role:kepala_bagian'])->prefix('kabag')->name('kabag.')->group(function () {
        Route::resource('tugas', KabagTugasController::class)
            ->only(['index', 'create', 'store']);

        Route::get('approval', [KabagApprovalController::class, 'index'])->name('approval.index');
        Route::post('approval/{laporan}', [KabagApprovalController::class, 'proses'])->name('approval.proses');
    });

    Route::middleware(['role:kepala_bps'])->prefix('kepala-bps')->name('kepala-bps.')->group(function () {
        Route::get('rekap', [RekapController::class, 'index'])->name('rekap');

        Route::resource('tugas', KepalaBpsTugasController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::get('approval', [KepalaBpsApprovalController::class, 'index'])->name('approval.index');
        Route::get('approval/{laporan}', [KepalaBpsApprovalController::class, 'show'])->name('approval.show');
        Route::post('approval/{laporan}', [KepalaBpsApprovalController::class, 'proses'])->name('approval.proses');
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
