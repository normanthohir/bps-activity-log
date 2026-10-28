<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Models\User;
use App\Support\KalenderPekerjaan;
use App\Support\LingkupLaporanTim;
use App\Support\ModeKerja;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // "/dashboard": mode Anggota (staf, dan kepala tim yang sedang di mode Anggota) serta admin.
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (ModeKerja::ketuaAktif($user)) {
            return $this->kabag($request);
        }

        return match ($user->role) {
            'staf', 'kepala_bagian' => view('dashboard.staf', $this->dataAnggota($request)),

            'kepala_bps' => $this->kepalaBps($request),

            'admin' => view('admin.dashboard', [
                'kalender' => KalenderPekerjaan::buat($request),
                'totalUser' => User::count(),
                'totalBagian' => Bagian::count(),
                'totalKepalaBagian' => User::whereHas('timDipimpin')->count(),
                'totalStaf' => User::where('role', 'staf')->count(),
                'belumVerifikasi' => User::whereNull('email_verified_at')->count(),
                'usersTerbaru' => User::with('bagian')->latest()->latest('id')->take(10)->get(),
                'bagianList' => Bagian::with('kepalaBagian')->withCount('anggotaTim')
                    ->orderBy('kode_bagian')->orderBy('nama_bagian')->get(),
            ]),

            default => abort(403, 'Role tidak dikenali.'),
        };
    }

    // "/dashboard/tim": mode Ketua Tim.
    public function kabag(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Di mode Anggota, arahkan ke dashboard biasa (bukan 403)
        if (! ModeKerja::ketuaAktif($user)) {
            return redirect()->route('dashboard');
        }

        $ids = ModeKerja::timDipimpinAktifIds($user);
        $bagian = $user->timDipimpin()->orderBy('kode_bagian')->first() ?? $user->bagian;
        $semuaTugas = Tugas::whereIn('bagian_id', $ids);
        $laporanTim = LingkupLaporanTim::untukKetua($user);

        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $hariIni = today()->toDateString();
        $akhirPekan = today()->isWeekend();

        $staf = User::whereIn('role', ['staf', 'kepala_bagian'])
            ->whereHas('tim', fn($q) => $q->whereIn('bagian.id', $ids))
            ->where('id', '!=', $user->id)
            ->orderBy('name')->get();

        // Siapa di tim yang belum mengirim laporan hari ini (hanya berarti di hari kerja)
        $sudahLapor = LaporanHarian::whereIn('user_id', $staf->pluck('id'))
            ->whereDate('tanggal', $hariIni)->where('status', '!=', 'draft')
            ->pluck('user_id')->unique();
        $belumLapor = $akhirPekan ? collect() : $staf->whereNotIn('id', $sudahLapor)->values();

        $disetujui = (clone $laporanTim)->whereBetween('tanggal', [$awal, $akhir])->where('status', 'disetujui')->count();
        $dikembalikan = (clone $laporanTim)->whereBetween('tanggal', [$awal, $akhir])->where('status', 'dikembalikan')->count();

        return view('dashboard.kepala-bagian', [
            'kalender' => KalenderPekerjaan::buat($request),
            'bagian' => $bagian,
            'namaTim' => $user->timDipimpin()->orderBy('kode_bagian')->pluck('nama_bagian')->implode(', '),
            'staf' => $staf,
            'akhirPekan' => $akhirPekan,
            'belumLapor' => $belumLapor,
            'menungguApproval' => (clone $laporanTim)->menunggu()->count(),
            'tingkatPersetujuan' => ($disetujui + $dikembalikan) > 0
                ? round($disetujui / ($disetujui + $dikembalikan) * 100)
                : null,
            'tugasTimSelesai' => (clone $semuaTugas)->where('status', 'selesai')->count(),
            'tugasTimDikerjakan' => (clone $semuaTugas)->where('status', 'dikerjakan')->count(),
            'tugasTimBelum' => (clone $semuaTugas)->where('status', 'belum_dikerjakan')->count(),
            'tugasTerlambat' => (clone $semuaTugas)->where('status', '!=', 'selesai')
                ->whereNotNull('tenggat')->where('tenggat', '<', $hariIni)->count(),
            'tugasTimTerbaru' => (clone $semuaTugas)->with('penerimaTugas')->latest()->take(5)->get(),
            'tugasSaya' => $user->tugasDiterima()->where('status', '!=', 'selesai')
                ->with('pemberiTugas')->latest()->take(5)->get(),
            'laporanMenunggu' => (clone $laporanTim)->where('user_id', '!=', $user->id)
                ->menunggu()->with('user')->latest('tanggal')->take(5)->get(),
        ]);
    }

    // "/dashboard/kantor": Kepala BPS.
    public function kepalaBps(Request $request): View
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        $user = $request->user();
        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();

        $rekapPerBagian = Bagian::withCount(['pegawai', 'anggotaTim'])
            ->orderBy('kode_bagian')->orderBy('nama_bagian')->get()
            ->map(function ($bagian) use ($awal, $akhir) {
                $bagian->total_laporan_bulan_ini = LaporanHarian::punyaBagian($bagian->id)
                    ->whereBetween('tanggal', [$awal, $akhir])->count();
                $bagian->menunggu_approval = LaporanHarian::punyaBagian($bagian->id)->menunggu()->count();

                return $bagian;
            });

        $bulanIni = fn() => LaporanHarian::whereBetween('tanggal', [$awal, $akhir]);

        return view('dashboard.kepala-bps', [
            'kalender' => KalenderPekerjaan::buat($request),
            'totalLaporanBulanIni' => $bulanIni()->count(),
            'menungguApproval' => LaporanHarian::menunggu()->count(),
            'disetujuiBulanIni' => $bulanIni()->where('status', 'disetujui')->count(),
            'dikembalikanBulanIni' => $bulanIni()->where('status', 'dikembalikan')->count(),
            'totalBagian' => Bagian::count(),
            'totalPegawai' => User::whereIn('role', ['staf', 'kepala_bagian'])->count(),
            'rekapPerBagian' => $rekapPerBagian,
            'laporanMenunggu' => LaporanHarian::menunggu()->with('user.bagian')->latest('tanggal')->take(5)->get(),
            'tugasSayaBerikan' => Tugas::where('dibuat_oleh', $user->id)
                ->with(['penerimaTugas', 'bagian'])->latest()->take(5)->get(),
        ]);
    }

    /** Data dashboard mode Anggota. */
    private function dataAnggota(Request $request): array
    {
        $user = $request->user();
        $awal = now()->startOfMonth()->toDateString();
        $akhir = now()->endOfMonth()->toDateString();
        $hariIni = today()->toDateString();

        $hariKerja = collect(CarbonPeriod::create($awal, $hariIni))
            ->reject(fn($h) => $h->isWeekend())->count();

        $hariLapor = $user->laporanHarian()
            ->whereBetween('tanggal', [$awal, $hariIni])
            ->where('status', '!=', 'draft')
            ->distinct()->count('tanggal');

        return [
            'kalender' => KalenderPekerjaan::buat($request),
            'timDipimpin' => $user->timDipimpin()->orderBy('kode_bagian')->orderBy('nama_bagian')->pluck('nama_bagian'),

            'sudahLaporHariIni' => $user->laporanHarian()->whereDate('tanggal', $hariIni)
                ->where('status', '!=', 'draft')->exists(),
            'hariLapor' => $hariLapor,
            'hariKerja' => $hariKerja,

            'tugasAktif' => $user->tugasDiterima()->where('status', '!=', 'selesai')->count(),
            'menungguApproval' => $user->laporanHarian()->where('status', 'menunggu')->count(),
            'laporanBulanIni' => $user->laporanHarian()->whereBetween('tanggal', [$awal, $akhir])
                ->where('status', '!=', 'draft')->count(),
            'disetujuiBulanIni' => $user->laporanHarian()->whereBetween('tanggal', [$awal, $akhir])
                ->where('status', 'disetujui')->count(),

            'dikembalikan' => $user->laporanHarian()->where('status', 'dikembalikan')->count(),
            'laporanDikembalikan' => $user->laporanHarian()->where('status', 'dikembalikan')
                ->with('logApproval')->latest('tanggal')->take(3)->get(),

            'laporanTerbaru' => $user->laporanHarian()->latest('tanggal')->latest('id')->take(5)->get(),
            'tugasAktifList' => $user->tugasDiterima()->where('status', '!=', 'selesai')
                ->with('pemberiTugas')->latest()->take(5)->get(),
        ];
    }
}
