<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KalenderPekerjaan;
use App\Support\LingkupLaporanTim;
use App\Support\ModeKerja;
use App\Support\PengingatLaporan;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * GET /api/dashboard
     * Otomatis memilih jenis dashboard sesuai role dan mode (sama seperti web):
     * ketua | kepala_bps | admin | anggota
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (ModeKerja::ketuaAktif($user)) {
            return $this->kirim('ketua', $this->dataKetua($request));
        }

        return match (true) {
            $user->isKepalaBps() => $this->kirim('kepala_bps', $this->dataKepalaBps($request)),
            $user->isAdmin() => $this->kirim('admin', $this->dataAdmin($request)),
            in_array($user->role, ['staf', 'kepala_bagian'], true)
                => $this->kirim('anggota', $this->dataAnggota($request)),
            default => abort(403, 'Role tidak dikenali.'),
        };
    }

    /** GET /api/dashboard/tim : khusus mode Ketua Tim. */
    public function tim(Request $request): JsonResponse
    {
        abort_unless(
            ModeKerja::ketuaAktif($request->user()),
            403,
            'Aktifkan mode Ketua Tim terlebih dahulu.'
        );

        return $this->kirim('ketua', $this->dataKetua($request));
    }

    /** GET /api/dashboard/kantor : khusus Kepala BPS. */
    public function kantor(Request $request): JsonResponse
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        return $this->kirim('kepala_bps', $this->dataKepalaBps($request));
    }

    private function kirim(string $jenis, array $data): JsonResponse
    {
        $user = request()->user();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'jenis' => $jenis,
                'mode' => ModeKerja::label($user),
                'dashboard' => $data,
            ],
        ]);
    }

    private function dataAnggota(Request $request): array
    {
        $user = $request->user();
        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $hariIni = today()->toDateString();

        $hariKerja = collect(CarbonPeriod::create($awal, $hariIni))
            ->reject(fn ($h) => $h->isWeekend())->count();

        $hariLapor = $user->laporanHarian()
            ->whereBetween('tanggal', [$awal, $hariIni])
            ->where('status', '!=', 'draft')
            ->distinct()->count('tanggal');

        return [
            'kalender' => KalenderPekerjaan::buat($request),
            'tim_dipimpin' => $user->timDipimpin()
                ->orderBy('kode_bagian')->orderBy('nama_bagian')
                ->pluck('nama_bagian')->values(),
            'sudah_lapor_hari_ini' => $user->laporanHarian()
                ->whereDate('tanggal', $hariIni)
                ->where('status', '!=', 'draft')->exists(),
            'hari_lapor' => $hariLapor,
            'hari_kerja' => $hariKerja,
            'pengingat_laporan' => PengingatLaporan::untuk($user),
            'pekerjaan_terlambat' => PengingatLaporan::terlambat($user),
            'tugas_aktif' => $user->tugasDiterima()->where('status', '!=', 'selesai')->count(),
            'menunggu_approval' => $user->laporanHarian()->where('status', 'menunggu')->count(),
            'laporan_bulan_ini' => $user->laporanHarian()
                ->whereBetween('tanggal', [$awal, $akhir])
                ->where('status', '!=', 'draft')->count(),
            'disetujui_bulan_ini' => $user->laporanHarian()
                ->whereBetween('tanggal', [$awal, $akhir])
                ->where('status', 'disetujui')->count(),
            'dikembalikan' => $user->laporanHarian()->where('status', 'dikembalikan')->count(),
            'laporan_dikembalikan' => $user->laporanHarian()
                ->where('status', 'dikembalikan')
                ->with('logApproval')->latest('tanggal')->take(3)->get(),
            'laporan_terbaru' => $user->laporanHarian()
                ->latest('tanggal')->latest('id')->take(5)->get(),
            'tugas_aktif_list' => $user->tugasDiterima()
                ->where('status', '!=', 'selesai')
                ->with('pemberiTugas')->latest()->take(5)->get(),
        ];
    }

    private function dataKetua(Request $request): array
    {
        $user = $request->user();
        $ids = ModeKerja::timDipimpinAktifIds($user);
        $bagian = $user->timDipimpin()->orderBy('kode_bagian')->first() ?? $user->bagian;
        $semuaTugas = Tugas::whereIn('bagian_id', $ids);
        $laporanTim = LingkupLaporanTim::untukKetua($user);

        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $hariIni = today()->toDateString();
        $akhirPekan = today()->isWeekend();

        $staf = User::whereIn('role', ['staf', 'kepala_bagian'])
            ->whereHas('tim', fn ($q) => $q->whereIn('bagian.id', $ids))
            ->where('id', '!=', $user->id)
            ->orderBy('name')->get();

        $sudahLapor = LaporanHarian::whereIn('user_id', $staf->pluck('id'))
            ->whereDate('tanggal', $hariIni)
            ->where('status', '!=', 'draft')
            ->pluck('user_id')->unique();
        $belumLapor = $akhirPekan ? collect() : $staf->whereNotIn('id', $sudahLapor)->values();

        $disetujui = (clone $laporanTim)->whereBetween('tanggal', [$awal, $akhir])
            ->where('status', 'disetujui')->count();
        $dikembalikan = (clone $laporanTim)->whereBetween('tanggal', [$awal, $akhir])
            ->where('status', 'dikembalikan')->count();

        return [
            'kalender' => KalenderPekerjaan::buat($request),
            'bagian' => $bagian,
            'nama_tim' => $user->timDipimpin()->orderBy('kode_bagian')
                ->pluck('nama_bagian')->implode(', '),
            'staf' => $staf,
            'akhir_pekan' => $akhirPekan,
            'belum_lapor' => $belumLapor,
            'menunggu_approval' => (clone $laporanTim)->menunggu()->count(),
            'tingkat_persetujuan' => ($disetujui + $dikembalikan) > 0
                ? round($disetujui / ($disetujui + $dikembalikan) * 100)
                : null,
            'tugas_tim_selesai' => (clone $semuaTugas)->where('status', 'selesai')->count(),
            'tugas_tim_dikerjakan' => (clone $semuaTugas)->where('status', 'dikerjakan')->count(),
            'tugas_tim_belum' => (clone $semuaTugas)->where('status', 'belum_dikerjakan')->count(),
            'tugas_terlambat' => (clone $semuaTugas)->where('status', '!=', 'selesai')
                ->whereNotNull('tenggat')->where('tenggat', '<', $hariIni)->count(),
            'tugas_tim_terbaru' => (clone $semuaTugas)->with('penerimaTugas')
                ->latest()->take(5)->get(),
            'tugas_saya' => $user->tugasDiterima()->where('status', '!=', 'selesai')
                ->with('pemberiTugas')->latest()->take(5)->get(),
            'laporan_menunggu' => (clone $laporanTim)->where('user_id', '!=', $user->id)
                ->menunggu()->with('user')->latest('tanggal')->take(5)->get(),
        ];
    }

    private function dataKepalaBps(Request $request): array
    {
        $user = $request->user();
        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();

        $rekapPerBagian = Bagian::withCount(['pegawai', 'anggotaTim'])
            ->orderBy('kode_bagian')->orderBy('nama_bagian')->get()
            ->map(function ($bagian) use ($awal, $akhir) {
                $bagian->total_laporan_bulan_ini = LaporanHarian::punyaBagian($bagian->id)
                    ->whereBetween('tanggal', [$awal, $akhir])->count();
                $bagian->menunggu_approval = LaporanHarian::punyaBagian($bagian->id)
                    ->menunggu()->count();

                return $bagian;
            });

        $bulanIni = fn () => LaporanHarian::whereBetween('tanggal', [$awal, $akhir]);

        return [
            'kalender' => KalenderPekerjaan::buat($request),
            'total_laporan_bulan_ini' => $bulanIni()->count(),
            'menunggu_approval' => LaporanHarian::menunggu()->count(),
            'disetujui_bulan_ini' => $bulanIni()->where('status', 'disetujui')->count(),
            'dikembalikan_bulan_ini' => $bulanIni()->where('status', 'dikembalikan')->count(),
            'total_bagian' => Bagian::count(),
            'total_pegawai' => User::whereIn('role', ['staf', 'kepala_bagian'])->count(),
            'rekap_per_bagian' => $rekapPerBagian,
            'laporan_menunggu' => LaporanHarian::menunggu()->with('user.bagian')
                ->latest('tanggal')->take(5)->get(),
            'tugas_saya_berikan' => Tugas::where('dibuat_oleh', $user->id)
                ->with(['penerimaTugas', 'bagian'])->latest()->take(5)->get(),
        ];
    }

    private function dataAdmin(Request $request): array
    {
        return [
            'kalender' => KalenderPekerjaan::buat($request),
            'total_user' => User::count(),
            'total_bagian' => Bagian::count(),
            'total_kepala_bagian' => User::whereHas('timDipimpin')->count(),
            'total_staf' => User::where('role', 'staf')->count(),
            'belum_verifikasi' => User::whereNull('email_verified_at')->count(),
            'users_terbaru' => User::with('bagian')->latest()->latest('id')->take(10)->get(),
            'bagian_list' => Bagian::with('kepalaBagian')->withCount('anggotaTim')
                ->orderBy('kode_bagian')->orderBy('nama_bagian')->get(),
        ];
    }
}