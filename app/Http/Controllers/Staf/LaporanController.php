<?php

namespace App\Http\Controllers\Staf;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    
    // Riwayat laporan milik user yang login
    public function index(Request $request): View
    {
        $laporan = $request->user()
            ->laporanHarian()
            ->latest('tanggal')
            ->paginate(15);

        return view('laporan.index', compact('laporan'));
    }

    public function create(Request $request): View
    {
        // Hanya tampilkan tugas milik sendiri yang belum selesai,
        // untuk pilihan dropdown "terkait tugas"
        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->where('status', '!=', 'selesai')
            ->get();

        return view('laporan.create', compact('tugasAktif'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['nullable', 'string'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', 'exists:tugas,id'],
            'file_lampiran' => ['nullable', 'url'],
            // tombol submit menentukan status: draft atau langsung diajukan
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        LaporanHarian::create($data);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    public function edit(LaporanHarian $laporan): View
    {
        $this->authorize('update', $laporan);

        $tugasAktif = auth()->user()->tugasDiterima()->where('status', '!=', 'selesai')->get();

        return view('laporan.edit', compact('laporan', 'tugasAktif'));
    }


    public function update(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['nullable', 'string'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', 'exists:tugas,id'],
            'file_lampiran' => ['nullable', 'url'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);

        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan->update($data);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }
}
