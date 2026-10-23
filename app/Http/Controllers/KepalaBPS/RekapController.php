<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Exports\RekapKinerjaExcel;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Services\RekapKinerjaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    public function index(Request $request, RekapKinerjaService $service): View
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);

        $rekap = $service->hitung($bulan, $tahun, $bagianId);
        $daftarBagian = Bagian::orderBy('nama_bagian')->get(['id', 'nama_bagian']);

        return view('rekap.index', compact('rekap', 'daftarBagian', 'bulan', 'tahun', 'bagianId'));
    }

    public function exportPdf(Request $request, RekapKinerjaService $service): Response
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);
        $rekap = $service->hitung($bulan, $tahun, $bagianId);

        return Pdf::loadView('rekap.pdf', ['rekap' => $rekap])
            ->setPaper('a4', 'landscape')
            ->download($this->namaFile($bulan, $tahun, 'pdf'));
    }

    public function exportExcel(Request $request, RekapKinerjaService $service): StreamedResponse
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);
        $rekap = $service->hitung($bulan, $tahun, $bagianId);

        return (new RekapKinerjaExcel())->unduh($rekap, $this->namaFile($bulan, $tahun, 'xlsx'));
    }

    /** Ambil bulan, tahun, dan bagian dari URL dengan nilai yang sudah dibatasi. */
    private function parameter(Request $request): array
    {
        $bulan = min(12, max(1, $request->integer('bulan', now()->month)));
        $tahun = min(now()->year + 1, max(now()->year - 5, $request->integer('tahun', now()->year)));
        $bagianId = $request->integer('bagian') ?: null;

        return [$bulan, $tahun, $bagianId];
    }

    private function namaFile(int $bulan, int $tahun, string $ekstensi): string
    {
        return sprintf('rekap-kinerja-%d-%02d.%s', $tahun, $bulan, $ekstensi);
    }
}