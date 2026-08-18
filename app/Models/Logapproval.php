<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogApproval extends Model
{
    use HasFactory;

    protected $table = 'log_approval';

    protected $fillable = [
        'laporan_id',
        'approver_id',
        'aksi',
        'catatan',
    ];

    // ── Relasi ──────────────────────────────────────────

    // Laporan yang diproses
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_id');
    }

    // Siapa yang melakukan aksi approve/reject ini
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}