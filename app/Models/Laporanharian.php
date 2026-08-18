<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harian';

    protected $fillable = [
        'user_id',
        'tugas_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'uraian',
        'output',
        'lokasi',
        'file_lampiran',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'disetujui_pada' => 'datetime',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    // Pemilik laporan (staf/kepala bagian yang mengisi)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Tugas terkait, jika laporan ini berasal dari penugasan (opsional)
    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    // Siapa yang menyetujui/menolak laporan ini
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    // Riwayat lengkap aksi approve/reject terhadap laporan ini
    // (bisa lebih dari satu kali: ditolak lalu direvisi lalu disetujui)
    public function logApproval(): HasMany
    {
        return $this->hasMany(LogApproval::class, 'laporan_id');
    }

    // ── Scope Query (mempermudah filter di Controller/Policy) ──

    // Contoh: LaporanHarian::punyaBagian($bagianId)->get();
    public function scopePunyaBagian($query, $bagianId)
    {
        return $query->whereHas('user', fn ($q) => $q->where('bagian_id', $bagianId));
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }
}