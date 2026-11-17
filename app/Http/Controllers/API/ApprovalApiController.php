<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\LaporanHarian;
use App\Models\User;
use App\Support\LingkupLaporanTim;
use App\Support\ModeKerja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalApiController extends Controller
{
    private const STATUS = ['menunggu', 'disetujui', 'dikembalikan'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->pastikanBerhak($user);
        $semuaBagian = $user->isKepalaBps();

        $awal = $akhir = null;
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) $request->input('periode'), $periode)) {
            $bulan = Carbon::create((int) $periode[1], (int) $periode[2], 1);
            $awal = $bulan->toDateString();
            $akhir = $bulan->copy()->endOfMonth()->toDateString();
        }

        $kata = addcslashes(trim((string) $request->input('cari')), '%_\\');
        $status = in_array($request->input('status'), self::STATUS, true)
            ? $request->input('status')
            : null;

        $dasar = $this->cakupan($user)
            ->whereIn('status', self::STATUS)
            ->where('user_id', '!=', $user->id)
            ->with(['user.bagian', 'tugas:id,judul'])
            ->when($kata !== '', function ($query) use ($kata) {
                $query->where(function ($query) use ($kata) {
                    $query->where('uraian', 'like', "%{$kata}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$kata}%"))
                        ->orWhereHas('tugas', fn ($tugas) => $tugas->where('judul', 'like', "%{$kata}%"));
                });
            })
            ->when($awal, fn ($query) => $query->whereBetween('tanggal', [$awal, $akhir]))
            ->when(
                $semuaBagian && $request->filled('bagian'),
                fn ($query) => $query->punyaBagian($request->integer('bagian'))
            );

        $ringkasan = collect(self::STATUS)
            ->mapWithKeys(fn ($status) => [$status => (clone $dasar)->where('status', $status)->count()]);

        $laporan = (clone $dasar)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar persetujuan laporan berhasil diambil.',
            'data' => [
                'laporan' => $laporan->items(),
                'ringkasan' => $ringkasan,
                'semua_bagian' => $semuaBagian,
                'daftar_bagian' => $semuaBagian
                    ? Bagian::orderBy('nama_bagian')->get(['id', 'nama_bagian'])
                    : collect(),
                'cakupan' => $this->labelCakupan($user),
                'pagination' => [
                    'current_page' => $laporan->currentPage(),
                    'last_page' => $laporan->lastPage(),
                    'per_page' => $laporan->perPage(),
                    'total' => $laporan->total(),
                    'from' => $laporan->firstItem(),
                    'to' => $laporan->lastItem(),
                    'next_page_url' => $laporan->nextPageUrl(),
                    'prev_page_url' => $laporan->previousPageUrl(),
                ],
            ],
        ]);
    }

    public function show(Request $request, LaporanHarian $laporan): JsonResponse
    {
        $user = $request->user();
        $this->pastikanBerhak($user);
        $this->authorize('approve', $laporan);
        abort_if($laporan->status === 'draft', 404);

        $laporan->load(['user.bagian', 'tugas.pemberiTugas', 'logApproval.approver']);

        return response()->json([
            'success' => true,
            'message' => 'Detail persetujuan laporan berhasil diambil.',
            'data' => ['laporan' => $laporan],
        ]);
    }

    public function proses(Request $request, LaporanHarian $laporan): JsonResponse
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
        $sudahDiproses = DB::transaction(function () use ($laporan, $user, $data, $disetujui) {
            $diubah = LaporanHarian::whereKey($laporan->getKey())
                ->where('status', 'menunggu')
                ->update([
                    'status' => $disetujui ? 'disetujui' : 'dikembalikan',
                    'disetujui_oleh' => $user->id,
                    'disetujui_pada' => now(),
                ]);

            if (!$diubah) {
                return true;
            }

            $laporan->logApproval()->create([
                'approver_id' => $user->id,
                'aksi' => $data['aksi'],
                'catatan' => filled($data['catatan'] ?? null) ? trim($data['catatan']) : null,
            ]);

            return false;
        });

        abort_if($sudahDiproses, 409, 'Laporan ini sudah diproses sebelumnya.');

        $laporan->refresh()->load(['user.bagian', 'tugas', 'logApproval.approver']);

        return response()->json([
            'success' => true,
            'message' => $disetujui ? 'Laporan disetujui.' : 'Laporan dikembalikan ke pegawai.',
            'data' => ['laporan' => $laporan],
        ]);
    }

    private function pastikanBerhak(User $user): void
    {
        abort_unless($user->isKepalaBps() || ModeKerja::ketuaAktif($user), 403);
    }

    private function cakupan(User $user): Builder
    {
        return $user->isKepalaBps()
            ? LaporanHarian::query()
            : LingkupLaporanTim::untukKetua($user);
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
