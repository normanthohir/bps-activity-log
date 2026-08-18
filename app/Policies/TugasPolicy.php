<?php

namespace App\Policies;

use App\Models\Tugas;
use App\Models\User;

class TugasPolicy
{
    // Kepala Bagian dan Kepala BPS boleh membuat tugas.
    // Kepala Bagian hanya boleh menugaskan ke staf DI BAGIANNYA SENDIRI.
    // Kepala BPS boleh menugaskan ke staf/kepala bagian di bagian mana pun.
    public function create(User $user, ?int $bagianTujuanId = null): bool
    {
        if ($user->isKepalaBps()) {
            return true;
        }

        if ($user->isKepalaBagian()) {
            return $bagianTujuanId === $user->bagian_id;
        }

        return false;
    }

    // Siapa boleh melihat detail tugas tertentu
    public function view(User $user, Tugas $tugas): bool
    {
        if ($user->isKepalaBps()) {
            return true;
        }

        // Kepala Bagian: boleh lihat tugas apa pun di bagiannya
        if ($user->isKepalaBagian()) {
            return $tugas->bagian_id === $user->bagian_id;
        }

        // Staf: hanya tugas yang ditujukan ke dirinya
        return $tugas->ditugaskan_ke === $user->id;
    }
}
