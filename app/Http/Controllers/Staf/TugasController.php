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
            ->when($request->filled('cari'), function ($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->string('cari') . '%');
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->string('status'));
            })
            // TAMBAHKAN BLOK KODE INI (Asumsi nama kolom di database Anda adalah 'tenggat')
            ->when($request->filled('bulan_tahun'), function ($q) use ($request) {
                // Mencari data yang waktu tenggatnya diawali dengan tahun-bulan terpilih (contoh: "2026-10%")
                $q->where('tenggat', 'like', $request->string('bulan_tahun') . '%');
            })
            ->latest('created_at')
            ->paginate(10)->withQueryString();

    

        return view('tugas.index-staf', compact('tugasAktif'));
    }


    public function show(Request $request, Tugas $tugas): View
    {
        abort_unless($tugas->ditugaskan_ke === $request->user()->id, 403);

        $tugas->load(['pemberiTugas', 'bagian', 'laporanHarian' => function ($q) {
            $q->latest();
        }]);

        return view('tugas.show-staf', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($tugas->ditugaskan_ke === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:belum_dikerjakan,dikerjakan,selesai'],
        ]);

        $tugas->update($data);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }
}
