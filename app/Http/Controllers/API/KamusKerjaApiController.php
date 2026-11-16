<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Support\ModeKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KamusKerjaApiController extends Controller
{
    private function bolehKelola(Request $request): void
    {
        abort_unless(
            $request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()),
            403
        );
    }

    private function timPilihan(Request $request)
    {
        return $request->user()->isAdmin()
            ? Bagian::orderBy('kode_bagian')->get()
            : $request->user()->timDipimpin()->orderBy('kode_bagian')->get();
    }

    private function bolehUbah(Request $request, KamusKerja $kamus): void
    {
        $this->bolehKelola($request);

        if ($request->user()->isAdmin()) {
            return;
        }

        abort_unless(
            $kamus->bagian_id
                && $request->user()->memimpinTim($kamus->bagian_id)
                && $kamus->dibuat_oleh === $request->user()->id,
            403
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->bolehKelola($request);

        $kamus = KamusKerja::tersediaUntuk($request->user())
            ->with('bagian')
            ->when(
                $request->filled('cari'),
                fn ($query) => $query->where('nama', 'like', '%' . $request->input('cari') . '%')
            )
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Kamus Kerja berhasil diambil.',
            'data' => [
                'kamus' => $kamus->items(),
                'tim_pilihan' => $this->timPilihan($request),
                'pagination' => [
                    'current_page' => $kamus->currentPage(),
                    'last_page' => $kamus->lastPage(),
                    'per_page' => $kamus->perPage(),
                    'total' => $kamus->total(),
                    'from' => $kamus->firstItem(),
                    'to' => $kamus->lastItem(),
                    'next_page_url' => $kamus->nextPageUrl(),
                    'prev_page_url' => $kamus->previousPageUrl(),
                ],
            ],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $this->bolehKelola($request);

        return response()->json([
            'success' => true,
            'message' => 'Referensi pembuatan Kamus Kerja berhasil diambil.',
            'data' => ['tim_pilihan' => $this->timPilihan($request)],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->bolehKelola($request);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'input' => 'nullable|string',
            'proses' => 'nullable|string',
            'output' => 'nullable|string',
            'bagian_id' => 'nullable|integer|exists:bagian,id',
        ]);

        $bagianId = $data['bagian_id'] ?? null;
        $this->periksaTim($request, $bagianId);
        $this->periksaDuplikat($data['nama'], $bagianId);

        $kamus = KamusKerja::create([
            ...$data,
            'dibuat_oleh' => $request->user()->id,
        ])->load('bagian');

        return response()->json([
            'success' => true,
            'message' => 'Entri Kamus Kerja ditambahkan.',
            'data' => ['kamus' => $kamus],
        ], 201);
    }

    private function periksaTim(Request $request, ?int $bagianId): void
    {
        if ($request->user()->isAdmin()) {
            if ($bagianId) {
                throw ValidationException::withMessages([
                    'bagian_id' => 'Admin membuat Kamus Kerja kantor; tidak perlu memilih tim.',
                ]);
            }

            return;
        }

        if (!$bagianId || !$request->user()->memimpinTim($bagianId)) {
            throw ValidationException::withMessages([
                'bagian_id' => 'Ketua Tim harus memilih salah satu tim yang dipimpinnya.',
            ]);
        }
    }

    private function periksaDuplikat(string $nama, ?int $bagianId, ?int $exceptId = null): void
    {
        $query = KamusKerja::where('nama', trim($nama));

        if ($bagianId === null) {
            $query->whereNull('bagian_id');
        } else {
            $query->where('bagian_id', $bagianId);
        }

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nama' => 'Nama ini sudah tersedia dalam Kamus Kerja untuk lingkup tersebut.',
            ]);
        }
    }

    public function show(Request $request, KamusKerja $kamus): JsonResponse
    {
        $this->bolehUbah($request, $kamus);
        $kamus->load('bagian');

        return response()->json([
            'success' => true,
            'message' => 'Detail Kamus Kerja berhasil diambil.',
            'data' => [
                'kamus' => $kamus,
                'tim_pilihan' => $this->timPilihan($request),
            ],
        ]);
    }

    public function update(Request $request, KamusKerja $kamus): JsonResponse
    {
        $this->bolehUbah($request, $kamus);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'input' => 'nullable|string',
            'proses' => 'nullable|string',
            'output' => 'nullable|string',
        ]);

        $this->periksaDuplikat($data['nama'], $kamus->bagian_id, $kamus->id);
        $kamus->update($data);
        $kamus->load('bagian');

        return response()->json([
            'success' => true,
            'message' => 'Kamus Kerja diperbarui.',
            'data' => ['kamus' => $kamus],
        ]);
    }

    public function destroy(Request $request, KamusKerja $kamus): JsonResponse
    {
        $this->bolehUbah($request, $kamus);
        $kamus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entri kamus dihapus; pekerjaan lama tetap ada.',
        ]);
    }
}
