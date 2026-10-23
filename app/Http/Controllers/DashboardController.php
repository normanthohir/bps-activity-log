<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Support\KalenderPekerjaan;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // "/dashboard" — dipakai staf & admin apa adanya.
    // Untuk kepala bagian & kepala BPS, otomatis dilempar ke URL
    // dashboard khusus mereka (/kabag/dashboard & /kepala-bps/dashboard),
    // supaya nama URL-nya konsisten dengan halaman lain milik role itu.
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Ketua tim bisa memiliki role login 'staf'; cek jabatan berbasis keanggotaan.
        if (\App\Support\ModeKerja::ketuaAktif($user)) {
            return redirect()->route('kabag.dashboard');
        }

        return match ($user->role) {
            'staf', 'kepala_bagian' => view('dashboard.staf', [
                'kalender' => KalenderPekerjaan::buat($request),
                'laporanTerbaru' => $user->laporanHarian()->latest('tanggal')->take(5)->get(),

                'tugasAktif' => $user->tugasDiterima()->where('status', '!=', 'selesai')->count(),
                'menungguApproval' => $user->laporanHarian()->where('status', 'menunggu')->count(),

                'laporanBulanIni' => $user->laporanHarian()->whereMonth('tanggal', now()->month)->count(),
                'disetujuiBulanIni' => $user->laporanHarian()
                    ->whereMonth('tanggal', now()->month)
                    ->where('status', 'disetujui')->count(),

                // 5 tugas aktif teratas, untuk preview di dashboard
                'tugasAktifList' => $user->tugasDiterima()
                    ->where('status', '!=', 'selesai')
                    ->with('pemberiTugas')
                    ->latest()
                    ->take(5)
                    ->get(),
            ]),


            'kepala_bps' => redirect()->route('kepala-bps.dashboard'),

            'admin' => view('admin.dashboard', [
                'kalender' => KalenderPekerjaan::buat($request),
                'totalUser' => User::count(),
                'totalBagian' => Bagian::count(),
                'totalKepalaBagian' => User::whereHas('timDipimpin')->count(),
                'usersTerbaru' => User::with('bagian')->latest()->take(10)->get(),
                'bagianList' => Bagian::with('kepalaBagian')->withCount('anggotaTim')->orderBy('kode_bagian')->orderBy('nama_bagian')->get(),
            ]),

            default => abort(403, 'Role tidak dikenali.'),
        };
    }

    // "/kabag/dashboard" — dashboard khusus kepala bagian.
    public function kabag(Request $request): View
    {
        abort_unless(\App\Support\ModeKerja::ketuaAktif($request->user()), 403);

        $user = $request->user();
        $ids = \App\Support\ModeKerja::timDipimpinAktifIds($user);
        $bagian = $user->timDipimpin()->orderBy('kode_bagian')->first() ?? $user->bagian;
        $semuaTugas = Tugas::whereIn('bagian_id', $ids);
        $laporanTim = \App\Support\LingkupLaporanTim::untukKetua($user);

        return view('dashboard.kepala-bagian', [
            'kalender' => KalenderPekerjaan::buat($request),
            'bagian' => $bagian,
            'namaTim' => $user->timDipimpin()->orderBy('kode_bagian')->pluck('nama_bagian')->implode(', '),
            'staf' => User::whereIn('role', ['staf', 'kepala_bagian'])->whereHas('tim',fn($q)=>$q->whereIn('bagian.id',$ids))->where('id', '!=', $user->id)->get(),
            'menungguApproval' => (clone $laporanTim)->menunggu()->count(),
            'tugasTimSelesai' => (clone $semuaTugas)->where('status','selesai')->count(),
            'tugasTimDikerjakan' => (clone $semuaTugas)->where('status','dikerjakan')->count(),
            'tugasTimBelum' => (clone $semuaTugas)->where('status','belum_dikerjakan')->count(),
            'tugasTimTerbaru' => (clone $semuaTugas)->with('penerimaTugas')->latest()->take(5)->get(),
            'tugasSaya' => $user->tugasDiterima()->where('status','!=','selesai')->latest()->take(5)->get(),
            'laporanMenunggu' => (clone $laporanTim)->where('user_id','!=',$user->id)
                ->menunggu()->with('user')->latest('tanggal')->take(5)->get(),
        ]);
    }
    // public function kabag(Request $request): View
    // {
    //     abort_unless($request->user()->isKepalaBagian(), 403);

    //     $user = $request->user();

    //     return view('dashboard.kepala-bagian', [
    //         'bagian' => $user->bagian,
    //         'staf' => $user->bagian->pegawai()->where('role', 'staf')->get(),
    //         'menungguApproval' => LaporanHarian::punyaBagian($user->bagian_id)
    //             ->menunggu()->count(),
    //     ]);
    // }

    // "/kepala-bps/dashboard" — dashboard khusus kepala BPS.
    // "/kepala-bps/dashboard" — dashboard khusus kepala BPS.
    public function kepalaBps(Request $request): View
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        $user = $request->user();

        // Rekap per bagian, sekalian dilengkapi jumlah laporan bulan ini
        // & yang masih menunggu approval per bagian (untuk badge di tabel)
        $rekapPerBagian = Bagian::withCount(['pegawai', 'anggotaTim'])
            ->orderBy('kode_bagian')->orderBy('nama_bagian')->get()
            ->map(function ($bagian) {
                $bagian->total_laporan_bulan_ini = LaporanHarian::punyaBagian($bagian->id)
                    ->whereMonth('tanggal', now()->month)
                    ->count();
                $bagian->menunggu_approval = LaporanHarian::punyaBagian($bagian->id)
                    ->menunggu()->count();

                return $bagian;
            });

        return view('dashboard.kepala-bps', [
            'kalender' => KalenderPekerjaan::buat($request),
            'totalLaporanBulanIni' => LaporanHarian::whereMonth('tanggal', now()->month)->count(),
            'menungguApproval' => LaporanHarian::menunggu()->count(),
            'disetujuiBulanIni' => LaporanHarian::whereMonth('tanggal', now()->month)
                ->where('status', 'disetujui')->count(),
            'dikembalikanBulanIni' => LaporanHarian::whereMonth('tanggal', now()->month)
                ->where('status', 'dikembalikan')->count(),

            'totalBagian' => Bagian::count(),
            'totalPegawai' => User::whereIn('role', ['staf', 'kepala_bagian'])->count(),

            'rekapPerBagian' => $rekapPerBagian,

            // Preview 5 laporan (dari SEMUA bagian) yang masih menunggu
            'laporanMenunggu' => LaporanHarian::menunggu()
                ->with('user.bagian')
                ->latest('tanggal')
                ->take(5)
                ->get(),

            // 5 tugas terbaru yang diberikan langsung oleh kepala BPS
            'tugasSayaBerikan' => Tugas::where('dibuat_oleh', $user->id)
                ->with(['penerimaTugas', 'bagian'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
