<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function index(): View
    {
        $rekapPerBagian = Bagian::withCount([
            'pegawai',
        ])->get()->map(function ($bagian) {
            $bagian->total_laporan = LaporanHarian::punyaBagian($bagian->id)
                ->whereMonth('tanggal', now()->month)
                ->count();
            return $bagian;
        });

        return view('rekap.index', compact('rekapPerBagian'));
    }
}
