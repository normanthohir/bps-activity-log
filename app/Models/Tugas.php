<?php

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use HasFactory, HasSqid;

    protected $table = 'tugas';

    protected $fillable = [
        'dibuat_oleh',
        'ditugaskan_ke',
        'bagian_id',
        'judul',
        'deskripsi',
        'tenggat',
        'status',
        'induk_tugas_id', 'input_tugas_id', 'sop_pelaksanaan_id', 'sop_langkah_id', 'kamus_kerja_id',
        'tanggal_mulai', 'jenis_input', 'uraian_input', 'nomor_surat',
        'asal_surat', 'tanggal_surat', 'file_input', 'output_rencana',
        'output_realisasi', 'file_output',
    ];

    protected function casts(): array
    {
        return [
            'tenggat' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_surat' => 'date',
        ];
    }

    /** Tugas induk khusus delegasi satu SOP utuh oleh Kepala BPS. */
    public function adalahIndukDelegasiSop(): bool
    {
        return $this->sop_pelaksanaan_id !== null
            && $this->sop_langkah_id === null
            && (int) $this->sopPelaksanaan?->tugas_induk_id === (int) $this->id;
    }

    /**
     * Semua output yang diperlukan sebagai input pekerjaan ini.
     * Dalam SOP multi-pelaksana, langkah sebelumnya dapat menghasilkan lebih dari satu output.
     * Sumber dibaca pada saat akses, sedangkan input_tugas_id tetap menjaga kompatibilitas lama.
     */
    public function semuaSumberOutput(): \Illuminate\Support\Collection
    {
        if ($this->jenis_input !== 'output_tugas') {
            return collect();
        }
        if ($this->sop_pelaksanaan_id && $this->sop_langkah_id) {
            $langkah = $this->sopLangkah;
            if ($langkah && $langkah->sumber_input === 'sebelumnya') {
                $sebelumnya = SopLangkah::where('sop_id', $langkah->sop_id)
                    ->where('urutan', '<', $langkah->urutan)->orderByDesc('urutan')->first();
                if ($sebelumnya) {
                    return self::where('sop_pelaksanaan_id', $this->sop_pelaksanaan_id)
                        ->where('sop_langkah_id', $sebelumnya->id)
                        ->with('penerimaTugas')->orderBy('id')->get();
                }
            }
        }
        return $this->inputDariTugas ? collect([$this->inputDariTugas]) : collect();
    }

    public function induk() { return $this->belongsTo(self::class, 'induk_tugas_id'); }
    public function disposisi() { return $this->hasMany(self::class, 'induk_tugas_id'); }
    public function inputDariTugas() { return $this->belongsTo(self::class, 'input_tugas_id'); }
    public function dipakaiSebagaiInput() { return $this->hasMany(self::class, 'input_tugas_id'); }
    public function kamusKerja() { return $this->belongsTo(KamusKerja::class, 'kamus_kerja_id'); }
    public function sopLangkah() { return $this->belongsTo(SopLangkah::class, 'sop_langkah_id'); }
    public function sopPelaksanaan() { return $this->belongsTo(SopPelaksanaan::class, 'sop_pelaksanaan_id'); }

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