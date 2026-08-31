<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Exports\RekapBagianExport;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        return view('rekap.index', [
            'rekapPerBagian' => $this->rekapData($bulan, $tahun),
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        $pdf = Pdf::loadView('rekap.pdf', [
            'rekapPerBagian' => $this->rekapData($bulan, $tahun),
            'namaBulan' => Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("rekap-kantor-{$tahun}-{$bulan}.pdf");
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        return Excel::download(
            new RekapBagianExport($this->rekapData($bulan, $tahun)),
            "rekap-kantor-{$tahun}-{$bulan}.xlsx"
        );
    }

    // Satu sumber data dipakai bareng oleh index(), exportPdf(), dan
    // exportExcel(), supaya angka yang ditampilkan & yang diekspor
    // selalu konsisten dan tidak perlu ditulis 3x.
    private function rekapData(int $bulan, int $tahun)
    {
        return Bagian::withCount(['pegawai'])
            ->get()
            ->map(function ($bagian) use ($bulan, $tahun) {
                $query = LaporanHarian::punyaBagian($bagian->id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);

                $bagian->total_laporan = (clone $query)->count();
                $bagian->disetujui = (clone $query)->where('status', 'disetujui')->count();
                $bagian->menunggu = (clone $query)->where('status', 'menunggu')->count();
                $bagian->dikembalikan = (clone $query)->where('status', 'dikembalikan')->count();

                return $bagian;
            });
    }
}
