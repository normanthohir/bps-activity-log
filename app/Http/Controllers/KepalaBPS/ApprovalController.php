<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    // TANPA filter bagian_id — inilah bedanya dengan versi Kepala Bagian.
    // Bisa juga difilter lewat query string ?bagian=id untuk drill-down.
    public function index(Request $request): View
    {
        $query = LaporanHarian::with(['user.bagian']);

        // Filter berdasarkan bagian (sudah ada sebelumnya)
        if ($request->filled('bagian')) {
            $query->punyaBagian($request->integer('bagian'));
        }

        // Filter tanggal spesifik (harian) — contoh: 2026-08-19
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->date('tanggal'));
        }

        // Filter bulan (terpisah dari tanggal harian) — contoh: bulan=8, tahun=2026
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->integer('bulan'));
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->integer('tahun'));
        }

        // Filter status — default tampilkan semua, tapi bisa dipersempit
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $laporan = $query->latest('tanggal')->paginate(15)->withQueryString();
        $daftarBagian = Bagian::all();

        return view('approval.index-kepala-bps', compact('laporan', 'daftarBagian'));
    }

    public function proses(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        // Policy approve() mengembalikan true untuk Kepala BPS tanpa syarat bagian
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
        $laporan->load([
            'user.bagian',
            'tugas.pemberiTugas',
            'logApproval.approver',
        ]);

        return view('approval.show-kepala-bps', compact('laporan'));
    }
}
