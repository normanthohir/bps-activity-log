<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'role',
        'bagian_id',
        'atasan_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    // Bagian/seksi tempat pegawai ini bekerja
    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }

    // Atasan langsung pegawai ini (self-referencing: users -> users)
    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    // Daftar bawahan langsung dari pegawai ini (kebalikan dari atasan())
    public function bawahan(): HasMany
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    // Bagian yang dipimpin, jika pegawai ini adalah kepala bagian
    public function bagianDipimpin(): HasMany
    {
        return $this->hasMany(Bagian::class, 'kepala_bagian_id');
    }

    // Semua laporan harian milik pegawai ini
    public function laporanHarian(): HasMany
    {
        return $this->hasMany(LaporanHarian::class);
    }

    // Tugas yang dibuat/diberikan oleh pegawai ini (jika kepala bagian/kepala BPS)
    public function tugasDibuat(): HasMany
    {
        return $this->hasMany(Tugas::class, 'dibuat_oleh');
    }

    // Tugas yang diterima oleh pegawai ini
    public function tugasDiterima(): HasMany
    {
        return $this->hasMany(Tugas::class, 'ditugaskan_ke');
    }

    // Laporan yang pernah disetujui/ditolak oleh pegawai ini (sebagai approver)
    public function riwayatApproval(): HasMany
    {
        return $this->hasMany(LogApproval::class, 'approver_id');
    }

    // ── Helper Role ─────────────────────────────────────
    // Mempermudah pengecekan role tanpa menulis string berulang di controller/blade

    public function isStaf(): bool
    {
        return $this->role === 'staf';
    }

    public function isKepalaBagian(): bool
    {
        return $this->role === 'kepala_bagian';
    }

    public function isKepalaBps(): bool
    {
        return $this->role === 'kepala_bps';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}