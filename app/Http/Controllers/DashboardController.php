<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Redirect logika berdasarkan role — inilah yang membuat
        // satu halaman "/dashboard" menampilkan tampilan berbeda
        // tanpa perlu URL terpisah untuk tiap role.
        return match ($user->role) {
            'staf' => view('dashboard.staf', [
                'laporanTerbaru' => $user->laporanHarian()->latest('tanggal')->take(5)->get(),
                'tugasAktif' => $user->tugasDiterima()->where('status', '!=', 'selesai')->count(),
                'menungguApproval' => $user->laporanHarian()->where('status', 'menunggu')->count(),
            ]),

            'kepala_bagian' => view('dashboard.kepala-bagian', [
                'bagian' => $user->bagian,
                'staf' => $user->bagian->pegawai()->where('role', 'staf')->get(),
                'menungguApproval' => \App\Models\LaporanHarian::punyaBagian($user->bagian_id)
                    ->menunggu()->count(),
            ]),

            'kepala_bps' => view('dashboard.kepala-bps', [
                'totalLaporanBulanIni' => \App\Models\LaporanHarian::whereMonth('tanggal', now()->month)->count(),
                'menungguApproval' => \App\Models\LaporanHarian::menunggu()->count(),
                'rekapPerBagian' => \App\Models\Bagian::withCount('pegawai')->get(),
            ]),

            'admin' => view('admin.dashboard', [
                'totalUser' => User::count(),
                'totalBagian' => Bagian::count(),
                'totalKepalaBagian' => User::where('role', 'kepala_bagian')->count(),
                'usersTerbaru' => User::with('bagian')->latest()->take(10)->get(),
                'bagianList' => Bagian::with('kepalaBagian')->withCount('pegawai')->get(),
            ]),

            default => abort(403, 'Role tidak dikenali.'),
        };
    }
}