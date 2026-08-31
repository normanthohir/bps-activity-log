<?php

namespace App\Http\Controllers\Staf;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
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

    public function show(Request $request, Tugas $tugas): View
    {
        // DIUBAH: Izinkan jika user adalah pemilik tugas ATAU user adalah Kepala Bagian/Pemberi Tugas
        // abort_unless(
        //     $tugas->ditugaskan_ke === auth()->id() || auth()->user()->isKepalaBagian(),
        //     403
        // );

        // $tugas->load(['pemberiTugas', 'bagian', 'laporanHarian' => fn($q) => $q->latest()]);

        // return view('tugas.show-staf', compact('tugas'));


        // abort_unless($request->user()->isKepalaBagian(), 403);

        $tugas->load(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
            $q->latest();
        }]);

        return view('tugas.show-staf', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($tugas->ditugaskan_ke === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:belum_dikerjakan,sedang_dikerjakan,selesai'],
        ]);

        $tugas->update($data);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }
}
