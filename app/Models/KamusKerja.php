<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KamusKerja extends Model
{
    protected $table = 'kamus_kerja';
    protected $fillable = ['nama','input','proses','output','bagian_id','dibuat_oleh'];

    public function bagian() { return $this->belongsTo(Bagian::class, 'bagian_id'); }
    public function pembuat() { return $this->belongsTo(User::class, 'dibuat_oleh'); }

    /** Kamus kantor milik Admin terlihat oleh semua, kamus tim hanya untuk tim yang dipimpin. */
    public function scopeTersediaUntuk(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isKepalaBps()) return $query;
        return $query->where(function (Builder $q) use ($user) {
            $q->whereNull('bagian_id');
            $tim = $user->timDipimpinIds();
            if ($tim) $q->orWhereIn('bagian_id', $tim);
        });
    }
}
