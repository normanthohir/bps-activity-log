<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Exports\RekapKinerjaExcel;
use App\Exports\RekapPekerjaanExcel;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Services\RekapKinerjaService;
use App\Services\RekapPekerjaanService;
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
    public function pekerjaan(Request $request, RekapPekerjaanService $service): View
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);

        $rekap = $service->hitung($bulan, $tahun, $bagianId);
        $daftarBagian = Bagian::orderBy('nama_bagian')->get(['id', 'nama_bagian']);

        return view('rekap.pekerjaan', compact('rekap', 'daftarBagian', 'bulan', 'tahun', 'bagianId'));
    }

    public function pekerjaanPdf(Request $request, RekapPekerjaanService $service): Response
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);
        $rekap = $service->hitung($bulan, $tahun, $bagianId);

        return Pdf::loadView('rekap.pekerjaan-pdf', ['rekap' => $rekap])
            ->setPaper('a4', 'landscape')
            ->download($this->namaFile($bulan, $tahun, 'pdf', 'rekap-pekerjaan'));
    }

    public function pekerjaanExcel(Request $request, RekapPekerjaanService $service): StreamedResponse
    {
        [$bulan, $tahun, $bagianId] = $this->parameter($request);
        $rekap = $service->hitung($bulan, $tahun, $bagianId);

        return (new RekapPekerjaanExcel())->unduh($rekap, $this->namaFile($bulan, $tahun, 'xlsx', 'rekap-pekerjaan'));
    }

    private function namaFile(int $bulan, int $tahun, string $ekstensi, string $awalan = 'rekap-kinerja'): string
    {
        return sprintf('%s-%d-%02d.%s', $awalan, $tahun, $bulan, $ekstensi);
    }
}
