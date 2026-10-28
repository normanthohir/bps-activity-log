<?php
namespace App\Support;

use App\Models\LaporanHarian;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LingkupLaporanTim
{
    /** Laporan bertugas mengikuti tim tugas; laporan mandiri mengikuti tim utama pegawai. */
    public static function untukKetua(User $ketua): Builder
    {
        $timIds = ModeKerja::timDipimpinAktifIds($ketua);
        return LaporanHarian::query()->where('user_id', '!=', $ketua->id)
            ->where(function ($q) use ($timIds) {
                $q->whereHas('tugas', fn ($t) => $t->whereIn('bagian_id', $timIds))
                    ->orWhere(fn ($t) => $t->whereNull('tugas_id')
                        ->whereHas('user', fn ($u) => $u->whereIn('bagian_id', $timIds)));
            });
    }
}
