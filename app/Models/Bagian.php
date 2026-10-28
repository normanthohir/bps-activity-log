<?php

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bagian extends Model
{
    use HasFactory, HasSqid;

    protected $table = 'bagian';

    protected $fillable = [
        'nama_bagian',
        'kode_bagian',
        'kepala_bagian_id',
    ];

    // ── Relasi ──────────────────────────────────────────

    // Siapa kepala bagian ini (relasi ke users)
    public function kepalaBagian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_bagian_id');
    }

    // Semua pegawai yang tergabung di bagian ini
    public function pegawai(): HasMany
    {
        return $this->hasMany(User::class, 'bagian_id');
    }

    public function anggotaTim(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bagian_user', 'bagian_id', 'user_id')->withPivot('peran_tim');
    }

    public function ketuaTim(): BelongsToMany
    {
        return $this->anggotaTim()->wherePivot('peran_tim', 'ketua');
    }

    // Semua tugas yang dibuat untuk bagian ini
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'bagian_id');
    }
}