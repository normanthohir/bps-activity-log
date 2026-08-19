<?php

namespace App\Http\Controllers\Staf;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
{
    // Daftar tugas aktif (belum selesai) milik staf yang login
    public function index(Request $request): View
    {
        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->with(['pemberiTugas', 'laporanHarian' => fn ($q) => $q->latest()])
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

    // Detail satu tugas: siapa yang kasih, deskripsi, tenggat,
    // dan riwayat laporan yang sudah pernah dibuat terkait tugas ini
    public function show(Tugas $tugas): View
    {
        // Pastikan staf hanya bisa lihat tugas miliknya sendiri
        abort_unless($tugas->ditugaskan_ke === auth()->id(), 403);

        $tugas->load(['pemberiTugas', 'bagian', 'laporanHarian' => fn ($q) => $q->latest()]);

        return view('tugas.show-staf', compact('tugas'));
    }
}
