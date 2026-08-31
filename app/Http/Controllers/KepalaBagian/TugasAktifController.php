<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasAktifController extends Controller
{
    public function index(Request $request): View
    {
        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->with(['pemberiTugas', 'laporanHarian' => fn($q) => $q->latest()])
            ->where('status', '!=', 'selesai')
            ->latest()
            ->get();

        $tugasSelesai = $request->user()
            ->tugasDiterima()
            ->where('status', 'selesai')
            ->latest()
            ->paginate(10, ['*'], 'selesai_page');

        return view('tugas.index-staf', compact('tugasAktif', 'tugasSelesai'));
    }

    public function show(Request $request, Tugas $tugas_aktif): View
    {
        abort_unless($request->user()->isKepalaBagian(), 403);

        $tugas_aktif->load(['pemberiTugas', 'bagian', 'laporanHarian' => fn($q) => $q->latest()]);

        return view('tugas.show-kabag', ['tugas' => $tugas_aktif]);
    }

    // public function show(Request $request, Tugas $tugas): View
    // {

    //     // abort_unless($request->user()->isKepalaBagian(), 403);

    //     $tugas->load(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
    //         $q->latest();
    //     }]);

    //     return view('tugas.show-kabag', compact('tugas'));
    // }


}
