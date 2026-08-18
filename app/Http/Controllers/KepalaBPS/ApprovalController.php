<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    // TANPA filter bagian_id — inilah bedanya dengan versi Kepala Bagian.
    // Bisa juga difilter lewat query string ?bagian=id untuk drill-down.
    public function index(Request $request): View
    {
        $query = LaporanHarian::menunggu()->with(['user.bagian']);

        if ($request->filled('bagian')) {
            $query->punyaBagian($request->integer('bagian'));
        }

        $laporan = $query->latest('tanggal')->paginate(15);
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
}
