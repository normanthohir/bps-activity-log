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
    // TANPA filter bagian_id — inilah bedanya dengan versi Kepala Bagian.
    // Bisa juga difilter lewat query string ?bagian=id untuk drill-down.
    public function index(Request $request): View
    {
        $filters = function ($query) use ($request) {
            if ($request->filled('bagian')) {
                $query->punyaBagian($request->integer('bagian'));
            }
            if ($request->filled('tanggal')) {
                $query->whereDate('tanggal', $request->date('tanggal'));
            }
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal', $request->integer('bulan'));
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal', $request->integer('tahun'));
            }
        };

        $query = LaporanHarian::with(['user.bagian'])->tap($filters);

        // Ringkasan jumlah per status, mengikuti filter bagian/tanggal yang
        // aktif tapi TIDAK ikut filter status — supaya kartu ringkasan tetap
        // menampilkan breakdown lengkap walau tabel di bawah sedang disaring.
        $totalMenunggu = (clone $query)->where('status', 'menunggu')->count();
        $totalDisetujui = (clone $query)->where('status', 'disetujui')->count();
        $totalDikembalikan = (clone $query)->where('status', 'dikembalikan')->count();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $laporan = $query->latest('tanggal')->paginate(15)->withQueryString();
        $daftarBagian = Bagian::all();

        return view('approval.index-kepala-bps', compact(
            'laporan',
            'daftarBagian',
            'totalMenunggu',
            'totalDisetujui',
            'totalDikembalikan'
        ));
    }

    public function proses(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        // Policy approve() mengembalikan true untuk Kepala BPS tanpa syarat bagian
        $this->authorize('approve', $laporan);

        $data = $request->validate([
            'aksi' => ['required', 'in:disetujui,ditolak'],
            'catatan' => ['required_if:aksi,ditolak', 'nullable', 'string'],
        ], [
            'catatan.required_if' => 'Catatan wajib diisi saat menolak laporan.',
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
