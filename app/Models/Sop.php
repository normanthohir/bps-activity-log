<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sop extends Model
{
    protected $table = 'sop';
    protected $fillable = ['kode', 'nama', 'versi', 'keterangan', 'dibuat_oleh', 'bagian_id'];
    public function bagian() { return $this->belongsTo(Bagian::class, 'bagian_id'); }
    public function pembuat() { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function langkah(): HasMany { return $this->hasMany(SopLangkah::class, 'sop_id')->orderBy('urutan'); }
    public function pelaksanaan(): HasMany { return $this->hasMany(SopPelaksanaan::class, 'sop_id'); }
}
