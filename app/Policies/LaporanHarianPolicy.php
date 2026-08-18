<?php

namespace App\Policies;

use App\Models\LaporanHarian;
use App\Models\User;

class LaporanHarianPolicy
{
    // Staf hanya boleh lihat laporan miliknya sendiri.
    // Kepala Bagian boleh lihat laporan siapa pun di bagiannya.
    // Kepala BPS boleh lihat semua laporan tanpa batas bagian.
    public function view(User $user, LaporanHarian $laporan): bool
    {
        if ($user->isKepalaBps()) {
            return true;
        }

        if ($user->isKepalaBagian()) {
            return $laporan->user->bagian_id === $user->bagian_id;
        }

        return $laporan->user_id === $user->id;
    }

    // Hanya pemilik laporan yang boleh mengedit, dan hanya
    // selama statusnya masih draft atau dikembalikan (belum final disetujui).
    public function update(User $user, LaporanHarian $laporan): bool
    {
        return $laporan->user_id === $user->id
            && in_array($laporan->status, ['draft', 'dikembalikan'], true);
    }

    // Aturan inti: siapa yang boleh approve/reject laporan tertentu.
    // - Kepala BPS: boleh approve laporan siapa saja, di bagian mana saja.
    // - Kepala Bagian: hanya boleh approve laporan staf DI BAGIANNYA SENDIRI,
    //   dan tidak boleh approve laporan miliknya sendiri.
    public function approve(User $user, LaporanHarian $laporan): bool
    {
        if ($laporan->user_id === $user->id) {
            return false; // tidak boleh approve laporan sendiri
        }

        if ($user->isKepalaBps()) {
            return true;
        }

        if ($user->isKepalaBagian()) {
            return $laporan->user->bagian_id === $user->bagian_id;
        }

        return false;
    }
}
