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

    public function show(LaporanHarian $laporan): View
    {
        $this->authorize('view', $laporan);

        $laporan->load(['tugas', 'logApproval.approver']);

        return view('laporan.show', compact('laporan'));
    }

    public function create(Request $request): View
    {
        // Hanya tampilkan tugas milik sendiri yang belum selesai,
        // untuk pilihan dropdown "terkait tugas"
        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->where('status', '!=', 'selesai')
            ->get();

        // Kalau halaman ini dibuka dari tombol "Buat laporan" di detail
        // tugas (?tugas=5), dropdown tugas otomatis ter-pilih duluan
        $tugasTerpilih = $request->integer('tugas');

        return view('laporan.create', compact('tugasAktif', 'tugasTerpilih'));
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
            'file_lampiran' => ['nullable', 'url', 'max:5120'],
            // tombol submit menentukan status: draft atau langsung diajukan
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan = LaporanHarian::create($data);

        // Kalau laporan ini terkait sebuah tugas, dan tugasnya masih
        // berstatus "belum_dikerjakan", otomatis update jadi "dikerjakan"
        // supaya kepala bagian/kepala BPS tahu progresnya sudah mulai jalan.
        if ($laporan->tugas_id) {
            Tugas::where('id', $laporan->tugas_id)
                ->where('status', 'belum_dikerjakan')
                ->update(['status' => 'dikerjakan']);
        }

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

    public function destroy(LaporanHarian $laporan): RedirectResponse
    {
        $this->authorize('delete', $laporan);

        $laporan->delete();

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }
}
