<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'dibuat_oleh',
        'ditugaskan_ke',
        'bagian_id',
        'judul',
        'deskripsi',
        'tenggat',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tenggat' => 'date',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    // Kepala bagian/kepala BPS yang memberi tugas ini
    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Staf yang menerima tugas ini
    public function penerimaTugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditugaskan_ke');
    }

    // Bagian tempat tugas ini berada
    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }

    // Semua laporan harian yang dibuat terkait tugas ini
    // (satu tugas bisa dilaporkan progresnya berkali-kali)
    public function laporanHarian(): HasMany
    {
        return $this->hasMany(LaporanHarian::class, 'tugas_id');
    }
}