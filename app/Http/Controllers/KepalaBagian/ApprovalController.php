<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    // Daftar laporan menunggu persetujuan, DIBATASI hanya bagian sendiri.
    // Pembatasan ini otomatis lewat scope punyaBagian() di model.
    public function index(Request $request): View
    {
        $laporan = LaporanHarian::punyaBagian($request->user()->bagian_id)
            ->menunggu()
            ->with('user')
            ->latest('tanggal')
            ->paginate(15);

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
}
