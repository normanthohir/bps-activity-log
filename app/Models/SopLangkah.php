<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopLangkah extends Model
{
    protected $table = 'sop_langkah';
    protected $fillable = ['sop_id', 'urutan', 'input', 'proses', 'output', 'sumber_input', 'durasi_hari'];
    public function sop() { return $this->belongsTo(Sop::class, 'sop_id'); }
}
