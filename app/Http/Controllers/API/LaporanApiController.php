<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LaporanHarian::with('tugas')
            ->where('user_id', $request->user()->id);

        if ($request->filled('cari')) {
            $cari = $request->input('cari');
            $query->where(function ($query) use ($cari) {
                $query->where('uraian', 'like', "%{$cari}%")
                    ->orWhereHas('tugas', fn ($tugas) => $tugas->where('judul', 'like', "%{$cari}%"));
            });
        }

        if ($request->filled('periode') && preg_match('/^\d{4}-\d{2}$/', $request->input('periode'))) {
            [$tahun, $bulan] = explode('-', $request->input('periode'));
            $query->whereYear('tanggal', (int) $tahun)
                ->whereMonth('tanggal', (int) $bulan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $laporan = $query->latest('tanggal')->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan berhasil diambil.',
            'data' => [
                'laporan' => $laporan->items(),
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

    public function create(Request $request): JsonResponse
    {
        $sudahDilaporkan = LaporanHarian::where('user_id', $request->user()->id)
            ->whereNotNull('tugas_id')
            ->select('tugas_id');

        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->whereNotIn('status', ['dikerjakan', 'belum_dikerjakan'])
            ->whereNotIn('tugas.id', $sudahDilaporkan)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Referensi laporan berhasil diambil.',
            'data' => [
                'tugas' => $tugasAktif,
                'tugas_terpilih' => $tugasAktif->firstWhere('id', $request->integer('tugas')),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['required', 'in:selesai,gagal,tertunda'],
            'alasan' => ['nullable', 'string'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', Rule::exists('tugas', 'id')->where('ditugaskan_ke', $request->user()->id)],
            'file_lampiran' => ['nullable', 'url'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);

        if (!empty($data['tugas_id'])) {
            $sudahAda = LaporanHarian::where('user_id', $request->user()->id)
                ->where('tugas_id', $data['tugas_id'])
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'tugas_id' => 'Pekerjaan ini sudah pernah dilaporkan.',
                ]);
            }
        }

        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan = LaporanHarian::create($data);

        if ($laporan->tugas_id) {
            Tugas::where('id', $laporan->tugas_id)
                ->where('status', 'belum_dikerjakan')
                ->update(['status' => 'dikerjakan']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan.',
            'data' => ['laporan' => $laporan->load('tugas')],
        ], 201);
    }

    public function show(LaporanHarian $laporan): JsonResponse
    {
        $this->authorize('view', $laporan);
        $laporan->load(['tugas', 'logApproval.approver']);

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan berhasil diambil.',
            'data' => ['laporan' => $laporan],
        ]);
    }

    public function edit(Request $request, LaporanHarian $laporan): JsonResponse
    {
        $this->authorize('update', $laporan);

        $sudahDilaporkan = LaporanHarian::where('user_id', $laporan->user_id)
            ->whereNotNull('tugas_id')
            ->where('id', '!=', $laporan->id)
            ->select('tugas_id');

        $tugasAktif = $request->user()->tugasDiterima()
            ->where(function ($query) use ($laporan, $sudahDilaporkan) {
                $query->where('tugas.id', $laporan->tugas_id)
                    ->orWhere(fn ($query) => $query->where('status', 'selesai')
                        ->whereNotIn('tugas.id', $sudahDilaporkan));
            })
            ->get();

        $laporan->load(['tugas', 'logApproval.approver']);

        return response()->json([
            'success' => true,
            'message' => 'Data revisi laporan berhasil diambil.',
            'data' => [
                'laporan' => $laporan,
                'tugas' => $tugasAktif,
            ],
        ]);
    }

    public function update(Request $request, LaporanHarian $laporan): JsonResponse
    {
        $this->authorize('update', $laporan);

        $data = $request->validate([
            'tanggal' => ['bail', 'required', 'date', function ($attribute, $value, $fail) use ($laporan) {
                $tanggalAsli = $laporan->tanggal->toDateString();
                $tanggalBaru = Carbon::parse($value)->toDateString();

                if ($tanggalBaru !== $tanggalAsli && $tanggalBaru < today()->toDateString()) {
                    $fail('Tanggal tidak boleh sebelum hari ini.');
                }
            }],
            'jam_mulai' => ['nullable', 'date_format:H:i,H:i:s'],
            'jam_selesai' => ['nullable', 'date_format:H:i,H:i:s', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['required', 'in:selesai,gagal,tertunda'],
            'alasan' => ['required_if:output,gagal,tertunda', 'nullable', 'string', 'max:1000'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', Rule::exists('tugas', 'id')->where('ditugaskan_ke', $request->user()->id)],
            'file_lampiran' => ['nullable', 'url'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ], [
            'alasan.required_if' => 'Alasan wajib diisi jika output gagal atau tertunda.',
        ]);

        $data['alasan'] = $data['output'] === 'selesai'
            ? null
            : trim((string) ($data['alasan'] ?? ''));
        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan->update($data);
        $laporan->load('tugas');

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui.',
            'data' => ['laporan' => $laporan],
        ]);
    }

    public function destroy(LaporanHarian $laporan): JsonResponse
    {
        $this->authorize('delete', $laporan);
        $laporan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }
}
