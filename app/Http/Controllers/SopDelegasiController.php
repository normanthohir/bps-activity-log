<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\SopLangkah;
use App\Models\SopPelaksanaan;
use App\Models\Tugas;
use App\Models\User;
use App\Support\ModeKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Alur baru: Kepala BPS -> 1 SOP utuh -> Ketua Tim -> langkah SOP -> Anggota Tim. */
class SopDelegasiController extends Controller
{
    private function bolehKepala(Request $request): void
    {
        abort_unless($request->user()->isKepalaBps(), 403,
            'Hanya Kepala BPS yang dapat mendelegasikan SOP kantor ke Ketua Tim.');
    }

    public function buat(Request $request, Sop $sop)
    {
        $this->bolehKepala($request);
        abort_unless($sop->bagian_id === null, 403, 'SOP Ketua Tim hanya digunakan oleh tim pembuat.');
        $sop->load('langkah');
        $tim = Bagian::with(['ketuaTim' => fn ($q) => $q->orderBy('name')])
            ->whereHas('ketuaTim')->orderBy('kode_bagian')->orderBy('nama_bagian')->get();

        return view('sop.delegasikan', compact('sop', 'tim'));
    }

    public function simpan(Request $request, Sop $sop)
    {
        $this->bolehKepala($request);
        abort_unless($sop->bagian_id === null, 403);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'bagian_id' => 'required|integer|exists:bagian,id',
            'ketua_id' => 'required|integer|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tenggat' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis_input' => 'required|in:manual,surat',
            'uraian_input' => 'nullable|string',
            'nomor_surat' => 'nullable|string|max:255',
            'asal_surat' => 'nullable|string|max:255',
            'tanggal_surat' => 'nullable|date',
            'file_input' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
            'output_rencana' => 'nullable|string',
        ]);

        $tim = Bagian::findOrFail($data['bagian_id']);
        $ketua = User::findOrFail($data['ketua_id']);
        if (!$ketua->isStaf() || !$tim->ketuaTim()->where('users.id', $ketua->id)->exists()) {
            throw ValidationException::withMessages(['ketua_id' => 'Pegawai yang dipilih bukan Ketua Tim pada tim tujuan.']);
        }
        if ($data['jenis_input'] === 'surat' && !$request->filled('nomor_surat') && !$request->filled('uraian_input') && !$request->hasFile('file_input')) {
            throw ValidationException::withMessages(['uraian_input' => 'Isi uraian, nomor surat, atau lampiran surat sebagai input SOP.']);
        }
        $sop->load('langkah');
        if ($sop->langkah->isEmpty()) {
            throw ValidationException::withMessages(['nama' => 'SOP belum memiliki langkah pekerjaan.']);
        }
        $outputRencana = $data['output_rencana'] ?? null;
        if (!$outputRencana) {
            $outputRencana = $sop->langkah->last()->output ?: 'Seluruh keluaran SOP '.$sop->kode.' selesai dan terdokumentasi.';
        }
        $tanggal = Carbon::parse($data['tanggal_mulai']);
        $tenggat = $data['tenggat'] ?? $tanggal->copy()->addDays($sop->langkah->sum('durasi_hari'))->toDateString();
        $berkas = $request->hasFile('file_input') ? $request->file('file_input')->store('pekerjaan', 'local') : null;

        try {
            $pelaksanaan = DB::transaction(function () use ($request, $data, $sop, $tim, $ketua, $outputRencana, $tenggat, $berkas) {
                $pelaksanaan = SopPelaksanaan::create([
                    'sop_id' => $sop->id,
                    'nama' => $data['nama'],
                    'dibuat_oleh' => $request->user()->id,
                ]);
                $induk = Tugas::create([
                    'sop_pelaksanaan_id' => $pelaksanaan->id,
                    'dibuat_oleh' => $request->user()->id,
                    'ditugaskan_ke' => $ketua->id,
                    'bagian_id' => $tim->id,
                    'judul' => $data['nama'],
                    'deskripsi' => 'Koordinasikan SOP '.$sop->kode.' ('.$sop->nama.') dan disposisikan setiap langkah kepada satu atau beberapa pelaksana, termasuk Ketua Tim sendiri, pada tim '.$tim->nama_bagian.'.',
                    'status' => 'belum_dikerjakan',
                    'jenis_input' => $data['jenis_input'],
                    'uraian_input' => $data['uraian_input'] ?? null,
                    'nomor_surat' => $data['jenis_input'] === 'surat' ? ($data['nomor_surat'] ?? null) : null,
                    'asal_surat' => $data['jenis_input'] === 'surat' ? ($data['asal_surat'] ?? null) : null,
                    'tanggal_surat' => $data['jenis_input'] === 'surat' ? ($data['tanggal_surat'] ?? null) : null,
                    'file_input' => $berkas,
                    'tanggal_mulai' => $data['tanggal_mulai'],
                    'tenggat' => $tenggat,
                    'output_rencana' => $outputRencana,
                ]);
                $pelaksanaan->update(['tugas_induk_id' => $induk->id]);
                return $pelaksanaan;
            });
        } catch (\Throwable $e) {
            if ($berkas) \Illuminate\Support\Facades\Storage::disk('local')->delete($berkas);
            throw $e;
        }

        return redirect()->route('pekerjaan.show', $pelaksanaan->tugasInduk)
            ->with('success', 'SOP utuh telah didisposisikan. Ketua Tim bisa membagikan tiap langkah kepada beberapa pelaksana, termasuk dirinya sendiri.');
    }

    private function tugasIndukUntukKetua(Request $request, SopPelaksanaan $pelaksanaan): Tugas
    {
        $induk = $pelaksanaan->tugasInduk;
        abort_unless($induk && $induk->sop_pelaksanaan_id === $pelaksanaan->id, 404);
        $user = $request->user();
        abort_unless(ModeKerja::ketuaAktif($user)
            && (int) $induk->ditugaskan_ke === (int) $user->id
            && $user->memimpinTim($induk->bagian_id), 403,
            'Masuk dengan mode Ketua Tim yang menerima disposisi SOP.');
        return $induk;
    }

    /** Daftar calon pelaksana: semua anggota tim ini + Ketua Tim penerima SOP itu sendiri. */
    private function calonPelaksana(Tugas $induk): \Illuminate\Support\Collection
    {
        $anggota = User::whereHas('tim', fn ($q) => $q->where('bagian.id', $induk->bagian_id))
            ->whereIn('role', ['staf', 'kepala_bagian'])
            ->orderBy('name')->get();

        $ketua = User::findOrFail($induk->ditugaskan_ke);
        return $anggota->push($ketua)->unique('id')->sortBy('name')->values();
    }

    /** Ketua Tim dapat memakai SOP yang dia buat, tanpa menunggu delegasi Kepala. */
    public function mulaiTim(Request $request, Sop $sop)
    {
        $user = $request->user();
        abort_unless(ModeKerja::ketuaAktif($user) && (int) $sop->dibuat_oleh === (int) $user->id
            && $sop->bagian_id && $user->memimpinTim($sop->bagian_id), 403);
        $sop->load('langkah');
        abort_if($sop->langkah->isEmpty(), 422, 'SOP belum mempunyai langkah.');
        $data = $request->validate(['nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date']);
        $pelaksanaan = DB::transaction(function () use ($request, $sop, $data, $user) {
            $pelaksanaan = SopPelaksanaan::create([
                'sop_id' => $sop->id, 'nama' => $data['nama'], 'dibuat_oleh' => $user->id,
            ]);
            $tanggal = Carbon::parse($data['tanggal_mulai']);
            $induk = Tugas::create([
                'sop_pelaksanaan_id' => $pelaksanaan->id,
                'dibuat_oleh' => $user->id, 'ditugaskan_ke' => $user->id,
                'bagian_id' => $sop->bagian_id, 'judul' => $data['nama'],
                'deskripsi' => 'Pelaksanaan SOP tim '.$sop->kode.' ('.$sop->nama.'). Ketua Tim membagikan tugas tiap langkah.',
                'status' => 'dikerjakan', 'jenis_input' => 'manual',
                'tanggal_mulai' => $tanggal->toDateString(),
                'tenggat' => $tanggal->copy()->addDays($sop->langkah->sum('durasi_hari'))->toDateString(),
                'output_rencana' => $sop->langkah->last()->output,
            ]);
            $pelaksanaan->update(['tugas_induk_id' => $induk->id]);
            return $pelaksanaan;
        });
        return redirect()->route('pekerjaan.create', [
            'pelaksanaan' => $pelaksanaan->id, 'langkah' => $sop->langkah->first()->id,
        ])->with('success', 'Pelaksanaan SOP untuk tim telah dibuat. Pilih pelaksana langkah pertama.');
    }

    /** Form "Beri Tugas" yang diisi otomatis dari SOP terpilih. */
    public function buatTugas(Request $request, SopPelaksanaan $pelaksanaan, SopLangkah $langkah)
    {
        $induk = $this->tugasIndukUntukKetua($request, $pelaksanaan);
        abort_unless((int) $langkah->sop_id === (int) $pelaksanaan->sop_id, 404);
        $pelaksanaan->load('sop.langkah');
        $existing = Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)
            ->where('sop_langkah_id', $langkah->id)->with('penerimaTugas')->get();
        $anggota = $this->calonPelaksana($induk)->whereNotIn('id', $existing->pluck('ditugaskan_ke')->all());
        $previous = $pelaksanaan->sop->langkah->where('urutan', '<', $langkah->urutan)->sortByDesc('urutan')->first();
        $next = $pelaksanaan->sop->langkah->where('urutan', '>', $langkah->urutan)->sortBy('urutan')->first();
        $waiting = $langkah->sumber_input === 'sebelumnya' && $previous
            && !Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)->where('sop_langkah_id', $previous->id)->exists();
        $locked = $next && $next->sumber_input === 'sebelumnya'
            && Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)->where('sop_langkah_id', $next->id)->exists();
        abort_if($induk->status === 'selesai' || $waiting || $locked || $anggota->isEmpty(), 409,
            'Langkah belum bisa dibagikan: periksa langkah sebelumnya, penugasan berikutnya, atau daftar pelaksana.');
        // Jadwal awal mengikuti urutan SOP, bukan semua langkah mulai bersamaan.
        $offset = $pelaksanaan->sop->langkah->where('urutan', '<', $langkah->urutan)->sum('durasi_hari');
        $awal = Carbon::parse($induk->tanggal_mulai ?? now())->addDays($offset);
        if ($previous) {
            $tenggatSumber = Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)
                ->where('sop_langkah_id', $previous->id)->max('tenggat');
            if ($tenggatSumber && Carbon::parse($tenggatSumber)->greaterThan($awal)) {
                $awal = Carbon::parse($tenggatSumber);
            }
        }
        $tenggat = $awal->copy()->addDays($langkah->durasi_hari);
        return view('pekerjaan.create-sop-langkah', compact('pelaksanaan', 'induk', 'langkah', 'anggota',
            'existing', 'awal', 'tenggat'));
    }

    public function langkah(Request $request, SopPelaksanaan $pelaksanaan)
    {
        $induk = $this->tugasIndukUntukKetua($request, $pelaksanaan);
        $pelaksanaan->load(['sop.langkah', 'tugas' => fn ($q) => $q->whereNotNull('sop_langkah_id')
            ->with('penerimaTugas')->orderBy('id')]);
        $tugasLangkah = $pelaksanaan->tugas->groupBy('sop_langkah_id');
        $anggota = $this->calonPelaksana($induk);

        return view('sop.disposisi-langkah', compact('pelaksanaan', 'induk', 'tugasLangkah', 'anggota'));
    }

    public function simpanLangkah(Request $request, SopPelaksanaan $pelaksanaan)
    {
        $induk = $this->tugasIndukUntukKetua($request, $pelaksanaan);

        // Kompatibilitas request lama yang hanya punya 'ditugaskan_ke'.
        $legacy = !$request->exists('penerima_ids') && $request->filled('ditugaskan_ke');
        if ($legacy) {
            $request->merge(['penerima_ids' => [$request->input('ditugaskan_ke')]]);
        }
        $data = $request->validate([
            'sop_langkah_id' => 'required|integer|exists:sop_langkah,id',
            'penerima_ids' => 'required|array|min:1|max:100',
            'penerima_ids.*' => 'required|integer|distinct|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tenggat' => 'required|date|after_or_equal:tanggal_mulai',
            'catatan' => 'nullable|string|max:5000',
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'uraian_input' => 'nullable|string',
            'output_rencana' => 'nullable|string',
        ]);

        $langkah = SopLangkah::findOrFail($data['sop_langkah_id']);
        if ((int) $langkah->sop_id !== (int) $pelaksanaan->sop_id) {
            throw ValidationException::withMessages(['sop_langkah_id' => 'Langkah bukan bagian dari SOP yang didelegasikan.']);
        }

        $calonIds = $this->calonPelaksana($induk)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $penerimaIds = array_map('intval', $data['penerima_ids']);
        if (array_diff($penerimaIds, $calonIds)) {
            throw ValidationException::withMessages([
                $legacy ? 'ditugaskan_ke' : 'penerima_ids' =>
                    'Pelaksana harus merupakan anggota tim penerima SOP atau Ketua Tim penerima SOP itu sendiri.',
            ]);
        }

        DB::transaction(function () use ($request, $pelaksanaan, $langkah, $data, $induk, $penerimaIds) {
            // Kunci pekerjaan induk: seluruh transaksi disposisi pada satu SOP diserialkan.
            $indukTerkunci = Tugas::whereKey($induk->id)->lockForUpdate()->firstOrFail();
            if ($indukTerkunci->status === 'selesai') {
                throw ValidationException::withMessages(['sop_langkah_id' => 'Tugas induk sudah selesai.']);
            }
            $sudahAda = Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)
                ->where('sop_langkah_id', $langkah->id)
                ->whereIn('ditugaskan_ke', $penerimaIds)->pluck('ditugaskan_ke')->all();
            if ($sudahAda) {
                throw ValidationException::withMessages(['sop_langkah_id' =>
                    'Sebagian pelaksana yang dipilih sudah mempunyai tugas pada langkah ini. Pilih hanya pelaksana baru.']);
            }

            $sebelumnya = $pelaksanaan->sop->langkah()
                ->where('urutan', '<', $langkah->urutan)->orderByDesc('urutan')->first();
            $jenisInput = $langkah->sumber_input;
            $inputDari = null;
            if ($jenisInput === 'sebelumnya') {
                if ($sebelumnya) {
                    $pekerjaanSumber = Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)
                        ->where('sop_langkah_id', $sebelumnya->id)->orderBy('id')->get();
                    if ($pekerjaanSumber->isEmpty()) {
                        throw ValidationException::withMessages(['sop_langkah_id' =>
                            'Disposisikan langkah sebelumnya terlebih dahulu. Seluruh output pelaksananya akan menjadi input langkah ini.']);
                    }
                    // Kolom lama tetap menunjuk sumber pertama; sumber lainnya dibaca dinamis
                    // dari seluruh pekerjaan dalam langkah sebelumnya pada pelaksanaan SOP ini.
                    $inputDari = $pekerjaanSumber->first();
                    $jenisInput = 'output_tugas';
                } else {
                    $jenisInput = $indukTerkunci->jenis_input === 'surat' ? 'surat' : 'manual';
                }
            }
            if (!$sebelumnya && $jenisInput === 'manual' && $indukTerkunci->jenis_input === 'surat') {
                $jenisInput = 'surat';
            }

            // Hindari menambah kewajiban baru setelah langkah berikutnya sudah didisposisikan:
            // pelaksana di langkah berikutnya sudah mengacu pada daftar output terdahulu.
            $langkahBerikutnya = $pelaksanaan->sop->langkah()
                ->where('urutan', '>', $langkah->urutan)->orderBy('urutan')->first();
            if ($langkahBerikutnya && $langkahBerikutnya->sumber_input === 'sebelumnya'
                && Tugas::where('sop_pelaksanaan_id', $pelaksanaan->id)
                    ->where('sop_langkah_id', $langkahBerikutnya->id)->exists()) {
                throw ValidationException::withMessages(['sop_langkah_id' =>
                    'Langkah berikutnya sudah didisposisikan. Pelaksana tambahan pada langkah ini tidak dapat ditambahkan lagi.']);
            }

            $uraianInput = array_key_exists('uraian_input', $data) ? $data['uraian_input'] : $langkah->input;
            // Instruksi awal dari Kepala BPS/surat tetap terbawa, meski uraian SOP sudah diedit.
            if (!$sebelumnya && $indukTerkunci->uraian_input
                && !str_contains((string) $uraianInput, (string) $indukTerkunci->uraian_input)) {
                $uraianInput = trim($indukTerkunci->uraian_input."\n".($uraianInput ?? ''));
            }
            foreach ($penerimaIds as $penerimaId) {
                Tugas::create([
                    'sop_pelaksanaan_id' => $pelaksanaan->id,
                    'sop_langkah_id' => $langkah->id,
                    'induk_tugas_id' => $indukTerkunci->id,
                    'dibuat_oleh' => $request->user()->id,
                    'ditugaskan_ke' => $penerimaId,
                    'bagian_id' => $indukTerkunci->bagian_id,
                    'judul' => $data['judul'] ?? \Illuminate\Support\Str::limit($pelaksanaan->nama.' — Langkah '.$langkah->urutan.' · '.$langkah->proses, 250),
                    'deskripsi' => ($data['deskripsi'] ?? $langkah->proses).(!empty($data['catatan']) ? "\n\nArahan Ketua Tim: ".$data['catatan'] : ''),
                    'status' => 'belum_dikerjakan',
                    'jenis_input' => $jenisInput,
                    'input_tugas_id' => $inputDari?->id,
                    'uraian_input' => $uraianInput,
                    'nomor_surat' => $jenisInput === 'surat' ? $indukTerkunci->nomor_surat : null,
                    'asal_surat' => $jenisInput === 'surat' ? $indukTerkunci->asal_surat : null,
                    'tanggal_surat' => $jenisInput === 'surat' ? $indukTerkunci->tanggal_surat : null,
                    'file_input' => $jenisInput === 'surat' ? $indukTerkunci->file_input : null,
                    'output_rencana' => $data['output_rencana'] ?? $langkah->output,
                    'tanggal_mulai' => $data['tanggal_mulai'],
                    'tenggat' => $data['tenggat'],
                ]);
            }
            if ($indukTerkunci->status === 'belum_dikerjakan') {
                $indukTerkunci->update(['status' => 'dikerjakan']);
            }
        });

        return redirect()->route('sop.delegasi.langkah', $pelaksanaan)
            ->with('success', 'Langkah '.$langkah->urutan.' berhasil diberikan kepada '.count($penerimaIds).' pelaksana.');
    }
}
