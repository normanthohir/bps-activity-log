<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Models\SopLangkah;
use App\Models\Tugas;
use App\Models\User;
use App\Support\AksesPekerjaan;
use App\Support\ModeKerja;
use App\Support\PengingatLaporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PekerjaanApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $hanyaTerlambat = $request->boolean('terlambat');
        $lingkup = AksesPekerjaan::lingkup($request->user());

        $jumlahTerlambat = (clone $lingkup)
            ->where('status', '!=', 'selesai')
            ->whereNotNull('tenggat')
            ->whereDate('tenggat', '<', today())
            ->count();

        $tugas = $lingkup
            ->with(['bagian', 'penerimaTugas', 'induk'])
            ->when(
                $request->filled('cari'),
                fn ($query) => $query->where('judul', 'like', '%' . $request->input('cari') . '%')
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->when(
                $hanyaTerlambat,
                fn ($query) => $query
                    ->where('status', '!=', 'selesai')
                    ->whereNotNull('tenggat')
                    ->whereDate('tenggat', '<', today())
                    ->orderBy('tenggat')
            )
            ->when(!$hanyaTerlambat, fn ($query) => $query->latest())
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pekerjaan berhasil diambil.',
            'data' => [
                'tugas' => $tugas->items(),
                'pagination' => [
                    'current_page' => $tugas->currentPage(),
                    'last_page' => $tugas->lastPage(),
                    'per_page' => $tugas->perPage(),
                    'total' => $tugas->total(),
                    'from' => $tugas->firstItem(),
                    'to' => $tugas->lastItem(),
                    'next_page_url' => $tugas->nextPageUrl(),
                    'prev_page_url' => $tugas->previousPageUrl(),
                ],
                'hanya_terlambat' => $hanyaTerlambat,
                'jumlah_terlambat' => $jumlahTerlambat,
                'pengingat_laporan' => PengingatLaporan::untuk($request->user()),
            ],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(AksesPekerjaan::bolehDisposisi($user), 403);

        $induk = null;
        if ($request->filled('induk')) {
            $induk = AksesPekerjaan::lingkup($user)->findOrFail($request->input('induk'));
            abort_unless(AksesPekerjaan::bolehDisposisi($user, $induk), 403);
            abort_if(
                $induk->adalahIndukDelegasiSop(),
                403,
                'Gunakan alur Delegasi Langkah SOP untuk pekerjaan induk SOP.'
            );
        }

        $bagian = AksesPekerjaan::timYangBolehDitugaskan($user);
        $penerima = User::whereIn('role', ['staf', 'kepala_bagian'])
            ->with(['tim:id'])
            ->orderBy('name')
            ->get()
            ->each(fn ($penerima) => $penerima->allowed_tim_ids = $penerima->tim
                ->pluck('id')
                ->unique()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all());

        if (ModeKerja::ketuaAktif($user) && !$penerima->contains('id', $user->id)) {
            $user->load('tim:id');
            $user->allowed_tim_ids = $user->tim
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
            $penerima->push($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Referensi pembuatan pekerjaan berhasil diambil.',
            'data' => [
                'bagian' => $bagian,
                'penerima' => $penerima,
                'sumber' => AksesPekerjaan::lingkup($user)
                    ->orderByDesc('id')
                    ->limit(350)
                    ->get(['id', 'judul', 'output_rencana', 'output_realisasi', 'status']),
                'daftar_kamus' => KamusKerja::tersediaUntuk($user)
                    ->orderBy('nama')
                    ->get(['id', 'nama', 'input', 'proses', 'output', 'bagian_id']),
                'induk' => $induk,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(AksesPekerjaan::bolehDisposisi($user), 403);

        if (!$request->exists('penerima_ids') && $request->filled('ditugaskan_ke')) {
            $request->merge(['penerima_ids' => [$request->input('ditugaskan_ke')]]);
        }

        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'sumber_judul' => ['sometimes', Rule::in(['manual', 'kamus'])],
            'kamus_kerja_id' => ['nullable', 'required_if:sumber_judul,kamus', 'integer', 'exists:kamus_kerja,id'],
            'bagian_id' => 'required|integer|exists:bagian,id',
            'penerima_ids' => 'required|array|min:1|max:100',
            'penerima_ids.*' => 'required|integer|distinct|exists:users,id',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tenggat' => 'nullable|date|after_or_equal:tanggal_mulai',
            'induk_tugas_id' => 'nullable|integer|exists:tugas,id',
            'jenis_input' => ['required', Rule::in(['manual', 'surat', 'output_tugas'])],
            'uraian_input' => 'nullable|string',
            'nomor_surat' => 'nullable|string|max:255',
            'asal_surat' => 'nullable|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'input_tugas_id' => 'nullable|integer|exists:tugas,id',
            'output_rencana' => 'nullable|string',
            'output_realisasi' => 'nullable|string',
            'file_input' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
            'file_output' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
        ]);

        $bagian = Bagian::findOrFail($data['bagian_id']);
        abort_unless(
            $user->isKepalaBps() || $user->isAdmin()
                || (ModeKerja::ketuaAktif($user) && $user->memimpinTim($bagian->id)),
            403,
            'Anda tidak dapat memberi tugas pada tim ini.'
        );

        if (($data['sumber_judul'] ?? 'manual') === 'kamus') {
            $kamus = KamusKerja::tersediaUntuk($user)->findOrFail($data['kamus_kerja_id']);
            if ($kamus->bagian_id && (int) $kamus->bagian_id !== (int) $bagian->id) {
                throw ValidationException::withMessages([
                    'kamus_kerja_id' => 'Entri Kamus Kerja ini hanya berlaku untuk tim pemiliknya.',
                ]);
            }
        } else {
            $data['kamus_kerja_id'] = null;
        }

        unset($data['sumber_judul']);
        $ids = array_map('intval', $data['penerima_ids']);
        $calon = User::whereIn('id', $ids)->get()->keyBy('id');
        foreach ($ids as $id) {
            $penerima = $calon->get($id);
            if (!$penerima || !AksesPekerjaan::tujuanValid($user, $penerima, $bagian)) {
                throw ValidationException::withMessages([
                    'penerima_ids' => 'Ada pelaksana yang bukan anggota tim tujuan atau Anda tidak berhak menugaskannya.',
                ]);
            }
        }

        if (!empty($data['induk_tugas_id'])) {
            $induk = AksesPekerjaan::lingkup($user)->findOrFail($data['induk_tugas_id']);
            abort_unless(AksesPekerjaan::bolehDisposisi($user, $induk), 403);
            if ($induk->adalahIndukDelegasiSop()) {
                throw ValidationException::withMessages([
                    'induk_tugas_id' => 'Gunakan menu Delegasi Langkah SOP untuk pekerjaan induk SOP.',
                ]);
            }
            if ((int) $induk->bagian_id !== (int) $bagian->id && !$user->isKepalaBps() && !$user->isAdmin()) {
                throw ValidationException::withMessages([
                    'bagian_id' => 'Disposisi turunan harus pada tim induknya.',
                ]);
            }
        }

        if ($data['jenis_input'] === 'output_tugas') {
            if (empty($data['input_tugas_id'])) {
                throw ValidationException::withMessages([
                    'input_tugas_id' => 'Pilih pekerjaan sumber output.',
                ]);
            }
            AksesPekerjaan::lingkup($user)->findOrFail($data['input_tugas_id']);
        } else {
            $data['input_tugas_id'] = null;
        }

        if (
            $data['jenis_input'] === 'surat'
            && empty($data['uraian_input'])
            && empty($data['nomor_surat'])
            && !$request->hasFile('file_input')
        ) {
            throw ValidationException::withMessages([
                'uraian_input' => 'Isi keterangan, nomor surat, atau unggah berkas.',
            ]);
        }

        $data['deskripsi'] = trim($data['deskripsi'] ?? '') ?: $data['judul'];
        $data['dibuat_oleh'] = $user->id;
        $data['status'] = 'belum_dikerjakan';
        unset($data['penerima_ids']);

        $berkas = [];
        try {
            foreach (['file_input', 'file_output'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('pekerjaan', 'local');
                    $berkas[] = $data[$field];
                } else {
                    unset($data[$field]);
                }
            }

            $idTugas = DB::transaction(function () use ($ids, $data): array {
                $idTugas = [];
                foreach ($ids as $id) {
                    $idTugas[] = Tugas::create([...$data, 'ditugaskan_ke' => $id])->id;
                }
                return $idTugas;
            });
        } catch (Throwable $exception) {
            foreach ($berkas as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        $tugas = Tugas::with(['bagian', 'penerimaTugas', 'induk'])
            ->whereIn('id', $idTugas)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil dibuat untuk setiap pelaksana.',
            'data' => ['tugas' => $tugas],
        ], 201);
    }

    public function show(Request $request, Tugas $tugas): JsonResponse
    {
        abort_unless(AksesPekerjaan::dapatLihat($request->user(), $tugas), 403);

        $tugas->load([
            'pemberiTugas',
            'penerimaTugas',
            'bagian',
            'induk',
            'inputDariTugas',
            'disposisi.penerimaTugas',
            'disposisi.bagian',
            'dipakaiSebagaiInput',
            'sopLangkah.sop',
            'sopPelaksanaan',
            'kamusKerja',
            'laporanHarian' => fn ($query) => $query->latest(),
        ]);

        $sumberOutput = $tugas->semuaSumberOutput();
        $pemakaiOutput = $tugas->dipakaiSebagaiInput;
        if ($tugas->sop_pelaksanaan_id && $tugas->sop_langkah_id && $tugas->sopLangkah) {
            $langkah = $tugas->sopLangkah;
            $berikut = SopLangkah::where('sop_id', $langkah->sop_id)
                ->where('urutan', '>', $langkah->urutan)
                ->orderBy('urutan')
                ->first();

            if ($berikut && $berikut->sumber_input === 'sebelumnya') {
                $pemakaiOutput = $pemakaiOutput->merge(
                    Tugas::where('sop_pelaksanaan_id', $tugas->sop_pelaksanaan_id)
                        ->where('sop_langkah_id', $berikut->id)
                        ->get()
                )->unique('id')->values();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pekerjaan berhasil diambil.',
            'data' => [
                'tugas' => $tugas,
                'sumber_output' => $sumberOutput,
                'pemakai_output' => $pemakaiOutput,
            ],
        ]);
    }

    public function update(Request $request, Tugas $tugas): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            AksesPekerjaan::dapatLihat($user, $tugas)
                && ($tugas->ditugaskan_ke === $user->id || $user->isKepalaBps() || $user->isAdmin()),
            403
        );

        $data = $request->validate([
            'status' => ['required', Rule::in(['belum_dikerjakan', 'dikerjakan', 'selesai'])],
            'output_realisasi' => 'nullable|string',
            'file_output' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
        ]);

        if ($data['status'] !== 'belum_dikerjakan' && $tugas->jenis_input === 'output_tugas') {
            $sumber = $tugas->semuaSumberOutput();
            if ($sumber->isEmpty() || $sumber->contains(fn ($pekerjaan) => $pekerjaan->status !== 'selesai')) {
                throw ValidationException::withMessages([
                    'status' => 'Seluruh pekerjaan pada langkah sumber input harus selesai sebelum pekerjaan ini dimulai atau diselesaikan.',
                ]);
            }
        }

        if (
            $data['status'] === 'selesai'
            && !$request->filled('output_realisasi')
            && !$request->hasFile('file_output')
            && !$tugas->file_output
        ) {
            throw ValidationException::withMessages([
                'output_realisasi' => 'Isi realisasi output atau unggah output sebelum selesai.',
            ]);
        }

        if ($data['status'] === 'selesai' && $tugas->adalahIndukDelegasiSop()) {
            $pelaksanaan = $tugas->sopPelaksanaan;
            $jumlahLangkah = $pelaksanaan->sop->langkah()->count();
            $jumlahDisposisi = $pelaksanaan->tugas()
                ->whereNotNull('sop_langkah_id')
                ->distinct()
                ->count('sop_langkah_id');
            if ($jumlahDisposisi < $jumlahLangkah) {
                throw ValidationException::withMessages([
                    'status' => 'Belum semua langkah SOP mempunyai pelaksana.',
                ]);
            }
        }

        if ($data['status'] === 'selesai' && $tugas->disposisi()->where('status', '!=', 'selesai')->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Masih ada disposisi bawahan yang belum selesai.',
            ]);
        }

        if ($request->hasFile('file_output')) {
            $data['file_output'] = $request->file('file_output')->store('pekerjaan', 'local');
        }

        $tugas->update($data);
        $tugas->load(['bagian', 'penerimaTugas', 'induk']);

        return response()->json([
            'success' => true,
            'message' => 'Progres dan output pekerjaan tersimpan.',
            'data' => ['tugas' => $tugas],
        ]);
    }

    private function bolehEditRincian(Request $request, Tugas $tugas): void
    {
        $user = $request->user();
        $boleh = $user->isAdmin() || $user->isKepalaBps()
            || (ModeKerja::ketuaAktif($user)
                && $user->memimpinTim($tugas->bagian_id)
                && (int) $tugas->dibuat_oleh === (int) $user->id)
            || (int) $tugas->ditugaskan_ke === (int) $user->id;

        abort_unless($boleh && AksesPekerjaan::dapatLihat($user, $tugas), 403);
        abort_if(
            $tugas->status === 'selesai',
            409,
            'Pekerjaan selesai tidak bisa diedit langsung. Hubungi penanggung jawab untuk revisi status.'
        );
    }

    public function editRincian(Request $request, Tugas $tugas): JsonResponse
    {
        $this->bolehEditRincian($request, $tugas);
        $tugas->load(['bagian', 'sopLangkah.sop', 'penerimaTugas']);

        return response()->json([
            'success' => true,
            'message' => 'Rincian pekerjaan berhasil diambil.',
            'data' => ['tugas' => $tugas],
        ]);
    }

    public function updateRincian(Request $request, Tugas $tugas): JsonResponse
    {
        $this->bolehEditRincian($request, $tugas);
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'uraian_input' => 'nullable|string',
            'output_rencana' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tenggat' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tugas->update($data);
        $tugas->load(['bagian', 'sopLangkah.sop', 'penerimaTugas']);

        return response()->json([
            'success' => true,
            'message' => 'Rincian pekerjaan diperbarui.',
            'data' => ['tugas' => $tugas],
        ]);
    }

    public function unduh(Request $request, Tugas $tugas, string $jenis): StreamedResponse
    {
        abort_unless(in_array($jenis, ['input', 'output'], true), 404);

        $boleh = AksesPekerjaan::dapatLihat($request->user(), $tugas);
        if (!$boleh && $jenis === 'output') {
            $boleh = Tugas::where('input_tugas_id', $tugas->id)
                ->whereIn('id', AksesPekerjaan::lingkup($request->user())->select('id'))
                ->exists();

            if (!$boleh && $tugas->sop_pelaksanaan_id && $tugas->sop_langkah_id && $tugas->sopLangkah) {
                $langkah = $tugas->sopLangkah;
                $berikut = SopLangkah::where('sop_id', $langkah->sop_id)
                    ->where('urutan', '>', $langkah->urutan)
                    ->orderBy('urutan')
                    ->first();
                if ($berikut && $berikut->sumber_input === 'sebelumnya') {
                    $boleh = AksesPekerjaan::lingkup($request->user())
                        ->where('sop_pelaksanaan_id', $tugas->sop_pelaksanaan_id)
                        ->where('sop_langkah_id', $berikut->id)
                        ->exists();
                }
            }
        }

        abort_unless($boleh, 403);
        $path = $jenis === 'input' ? $tugas->file_input : $tugas->file_output;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
