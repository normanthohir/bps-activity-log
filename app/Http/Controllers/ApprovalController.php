<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\User;
use App\Support\LingkupLaporanTim;
use App\Support\ModeKerja;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Persetujuan laporan harian untuk dua peran:
 *  - Kepala BPS : seluruh laporan di semua tim
 *  - Ketua Tim  : laporan anggota tim yang dipimpinnya (mode Ketua)
 * Route kabag.approval.* dan kepala-bps.approval.* sama-sama menunjuk ke sini.
 */
class ApprovalController extends Controller
{
    private const STATUS = ['menunggu', 'disetujui', 'dikembalikan'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->pastikanBerhak($user);
        $semuaBagian = $user->isKepalaBps();

        // Periode "YYYY-MM" dari input type="month", dipakai sebagai rentang tanggal
        $awal = $akhir = null;
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) $request->input('periode'), $m)) {
            $bulan = Carbon::create((int) $m[1], (int) $m[2], 1);
            $awal = $bulan->toDateString();
            $akhir = $bulan->copy()->endOfMonth()->toDateString();
        }

        $kata = addcslashes(trim((string) $request->input('cari')), '%_\\');
        $status = in_array($request->input('status'), self::STATUS, true) ? $request->input('status') : null;

        // Filter dasar (tanpa status). Dipakai juga untuk kartu ringkasan.
        $dasar = $this->cakupan($user)
            ->whereIn('status', self::STATUS)          // draft belum dikirim, jadi tidak tampil
            ->where('user_id', '!=', $user->id)        // tidak ada yang menyetujui laporannya sendiri
            ->with(['user.bagian', 'tugas:id,judul'])
            ->when($kata !== '', function ($q) use ($kata) {
                $q->where(function ($w) use ($kata) {
                    $w->where('uraian', 'like', "%{$kata}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$kata}%"))
                        ->orWhereHas('tugas', fn ($t) => $t->where('judul', 'like', "%{$kata}%"));
                });
            })
            ->when($awal, fn ($q) => $q->whereBetween('tanggal', [$awal, $akhir]))
            ->when($semuaBagian && $request->filled('bagian'), fn ($q) => $q->punyaBagian($request->integer('bagian')));

        $ringkasan = collect(self::STATUS)
            ->mapWithKeys(fn ($s) => [$s => (clone $dasar)->where('status', $s)->count()]);

        $laporan = $dasar
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('tanggal')->latest('id')
            ->paginate(25)->withQueryString();

        return view('approval.index', [
            'laporan' => $laporan,
            'ringkasan' => $ringkasan,
            'semuaBagian' => $semuaBagian,
            'daftarBagian' => $semuaBagian ? Bagian::orderBy('nama_bagian')->get(['id', 'nama_bagian']) : collect(),
            'cakupan' => $this->labelCakupan($user),
            'rute' => $this->rute($user),
        ]);
    }

    public function show(Request $request, LaporanHarian $laporan): View
    {
        $user = $request->user();
        $this->pastikanBerhak($user);
        $this->authorize('approve', $laporan); // bukan laporan sendiri, dan masih dalam lingkup tim

        abort_if($laporan->status === 'draft', 404);

        $laporan->load(['user.bagian', 'tugas.pemberiTugas', 'logApproval.approver']);

        return view('approval.show', [
            'laporan' => $laporan,
            'rute' => $this->rute($user),
        ]);
    }

    public function proses(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        $user = $request->user();
        $this->pastikanBerhak($user);
        $this->authorize('approve', $laporan);

        $data = $request->validate([
            'aksi' => ['required', 'in:disetujui,ditolak'],
            'catatan' => ['required_if:aksi,ditolak', 'nullable', 'string', 'max:1000'],
        ], [
            'catatan.required_if' => 'Catatan wajib diisi saat mengembalikan laporan.',
        ]);

        $disetujui = $data['aksi'] === 'disetujui';

        // Pembaruan bersyarat: hanya berhasil kalau laporan masih "menunggu".
        // Mencegah laporan yang sama diproses dua kali (klik ganda atau dua atasan sekaligus).
        $sudahDiproses = DB::transaction(function () use ($laporan, $user, $data, $disetujui) {
            $diubah = LaporanHarian::whereKey($laporan->getKey())
                ->where('status', 'menunggu')
                ->update([
                    'status' => $disetujui ? 'disetujui' : 'dikembalikan',
                    'disetujui_oleh' => $user->id,
                    'disetujui_pada' => now(),
                ]);

            if (! $diubah) {
                return true;
            }

            $laporan->logApproval()->create([
                'approver_id' => $user->id,
                'aksi' => $data['aksi'],
                'catatan' => filled($data['catatan'] ?? null) ? trim($data['catatan']) : null,
            ]);

            return false;
        });

        if ($sudahDiproses) {
            return back()->with('error', 'Laporan ini sudah diproses sebelumnya.');
        }

        return back()->with('success', $disetujui ? 'Laporan disetujui.' : 'Laporan dikembalikan ke pegawai.');
    }

    // ── Pembantu ─────────────────────────────────────────────

    private function pastikanBerhak(User $user): void
    {
        abort_unless($user->isKepalaBps() || ModeKerja::ketuaAktif($user), 403);
    }

    /** Kepala BPS melihat semua laporan, Ketua Tim hanya laporan timnya. */
    private function cakupan(User $user)
    {
        return $user->isKepalaBps()
            ? LaporanHarian::query()
            : LingkupLaporanTim::untukKetua($user);
    }

    /** Awalan nama route sesuai peran (route tetap bernama lama). */
    private function rute(User $user): string
    {
        return $user->isKepalaBps() ? 'kepala-bps.approval' : 'kabag.approval';
    }

    private function labelCakupan(User $user): string
    {
        if ($user->isKepalaBps()) {
            return 'Semua Tim';
        }

        return $user->timDipimpin()->orderBy('kode_bagian')->orderBy('nama_bagian')
            ->pluck('nama_bagian')->implode(', ') ?: ($user->bagian?->nama_bagian ?? 'Tim Anda');
    }
}