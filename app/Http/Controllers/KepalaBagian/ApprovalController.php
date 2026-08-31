<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    // Daftar laporan menunggu persetujuan, DIBATASI hanya bagian sendiri,
    // dan TIDAK menampilkan laporan milik kepala bagian sendiri
    // (kepala bagian tidak approve laporannya sendiri).
    public function index(Request $request): View
    {
        $query = LaporanHarian::punyaBagian($request->user()->bagian_id)
            ->where('user_id', '!=', $request->user()->id)
            ->with('user');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->date('tanggal'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->integer('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->integer('tahun'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $laporan = $query->latest('tanggal')->paginate(15)->withQueryString();

        return view('approval.index', compact('laporan'));
    }

    public function proses(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        // authorize() otomatis memanggil LaporanHarianPolicy@approve
        // yang sudah mengecek: bukan laporan sendiri, dan harus 1 bagian.
        $this->authorize('approve', $laporan);

        $data = $request->validate([
            'aksi' => ['required', 'in:disetujui,ditolak'],
            'catatan' => ['nullable', 'string'],
        ]);

        $laporan->update([
            'status' => $data['aksi'] === 'disetujui' ? 'disetujui' : 'dikembalikan',
            'disetujui_oleh' => $request->user()->id,
            'disetujui_pada' => now(),
        ]);

        $laporan->logApproval()->create([
            'approver_id' => $request->user()->id,
            'aksi' => $data['aksi'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('success', 'Laporan berhasil diproses.');
    }

    public function show(LaporanHarian $laporan): View
    {
        // Pakai aturan yang sama dengan proses(): bukan laporan sendiri,
        // dan harus satu bagian dengan kepala bagian yang login. Ini juga
        // menutup celah kalau kepala bagian coba buka detail laporan
        // bagian lain atau laporannya sendiri lewat akses URL langsung.
        $this->authorize('approve', $laporan);

        $laporan->load([
            'user.bagian',
            'tugas.pemberiTugas',
            'logApproval.approver',
        ]);

        return view('approval.show', compact('laporan'));
    }
}