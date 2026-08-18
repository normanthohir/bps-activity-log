<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bagian extends Model
{
    use HasFactory;

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

    // Semua tugas yang dibuat untuk bagian ini
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'bagian_id');
    }
}