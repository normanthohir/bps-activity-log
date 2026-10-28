<?php
namespace App\Policies;

use App\Models\Tugas;
use App\Models\User;
use App\Support\AksesPekerjaan;

class TugasPolicy
{
    public function create(User $user, ?int $bagianTujuanId = null): bool
    {
        if (!AksesPekerjaan::bolehDisposisi($user)) return false;
        return $bagianTujuanId === null || AksesPekerjaan::timYangBolehDitugaskan($user)->contains('id',$bagianTujuanId);
    }
    public function view(User $user,Tugas $tugas): bool
    {
        return AksesPekerjaan::dapatLihat($user,$tugas);
    }
}
