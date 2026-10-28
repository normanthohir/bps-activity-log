<?php
namespace App\Models;

use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Model;

class SopPelaksanaan extends Model
{
     use HasSqid;
    protected $table = 'sop_pelaksanaan';
    protected $fillable = ['sop_id','dibuat_oleh','nama','tugas_induk_id'];
    public function sop() { return $this->belongsTo(Sop::class, 'sop_id'); }
    public function tugasInduk() { return $this->belongsTo(Tugas::class, 'tugas_induk_id'); }
    public function tugas() { return $this->hasMany(Tugas::class, 'sop_pelaksanaan_id'); }
}
