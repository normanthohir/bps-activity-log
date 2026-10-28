<?php
namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Models\Sop;
use App\Models\SopLangkah;
use App\Models\SopPelaksanaan;
use App\Support\ModeKerja;
use App\Models\Tugas;
use App\Models\User;
use App\Support\AksesPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $tugas = AksesPekerjaan::lingkup($request->user())->with(['bagian','penerimaTugas','induk'])
            ->when($request->filled('cari'), fn ($q) => $q->where('judul','like','%'.$request->input('cari').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status',$request->input('status')))
            ->latest()->paginate(20)->withQueryString();
        return view('pekerjaan.index', compact('tugas'));
    }

    private function referensi(Request $request): array
    {
        $user = $request->user();
        $bagian = AksesPekerjaan::timYangBolehDitugaskan($user);
        // Tambahkan ketua sendiri sebagai kandidat; validasi di server tetap membatasi tim yang dipimpin.
        $penerima = User::whereIn('role',['staf','kepala_bagian'])->with(['tim:id'])->orderBy('name')->get()
            ->each(fn ($p) => $p->allowed_tim_ids = $p->tim->pluck('id')
                ->unique()->map(fn ($id) => (string) $id)->values()->all());
        if (\App\Support\ModeKerja::ketuaAktif($user) && !$penerima->contains('id', $user->id)) {
            $user->load('tim:id');
            $user->allowed_tim_ids = $user->tim->pluck('id')->map(fn ($id) => (string) $id)->all();
            $penerima->push($user);
        }
        $sumber = AksesPekerjaan::lingkup($user)->orderByDesc('id')->limit(350)->get(['id','judul','output_rencana','output_realisasi','status']);
        $daftarKamus = KamusKerja::tersediaUntuk($user)->orderBy('nama')
            ->get(['id','nama','input','proses','output','bagian_id']);
        return compact('bagian','penerima','sumber','daftarKamus');
    }

    public function create(Request $request)
    {
        abort_unless(AksesPekerjaan::bolehDisposisi($request->user()),403);
        if ($request->filled('pelaksanaan')) {
            $request->validate(['pelaksanaan' => 'required|integer|exists:sop_pelaksanaan,id',
                'langkah' => 'required|integer|exists:sop_langkah,id']);
            return app(SopDelegasiController::class)->buatTugas($request,
                SopPelaksanaan::findOrFail($request->integer('pelaksanaan')),
                SopLangkah::findOrFail($request->integer('langkah')));
        }
        $induk = $request->filled('induk') ? AksesPekerjaan::lingkup($request->user())->findOrFail($request->input('induk')) : null;
        abort_unless(AksesPekerjaan::bolehDisposisi($request->user(),$induk),403);
        abort_if($induk?->adalahIndukDelegasiSop(),403,'Gunakan halaman Disposisi Langkah SOP.');
        return view('pekerjaan.create', [...$this->referensi($request), 'induk'=>$induk]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless(AksesPekerjaan::bolehDisposisi($user), 403);
        // Kompatibilitas formulir lama / aplikasi eksternal yang mengirim satu penerima.
        if (!$request->exists('penerima_ids') && $request->filled('ditugaskan_ke')) {
            $request->merge(['penerima_ids' => [$request->input('ditugaskan_ke')]]);
        }
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'sumber_judul' => ['sometimes', Rule::in(['manual','kamus'])],
            'kamus_kerja_id' => ['nullable','required_if:sumber_judul,kamus','integer','exists:kamus_kerja,id'],
            'bagian_id' => 'required|integer|exists:bagian,id',
            'penerima_ids' => 'required|array|min:1|max:100',
            'penerima_ids.*' => 'required|integer|distinct|exists:users,id',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tenggat' => 'nullable|date|after_or_equal:tanggal_mulai',
            'induk_tugas_id' => 'nullable|integer|exists:tugas,id',
            'jenis_input' => ['required', Rule::in(['manual','surat','output_tugas'])],
            'uraian_input' => 'nullable|string', 'nomor_surat' => 'nullable|string|max:255',
            'asal_surat' => 'nullable|string|max:255', 'tanggal_surat' => 'nullable|date',
            'input_tugas_id' => 'nullable|integer|exists:tugas,id',
            'output_rencana' => 'nullable|string', 'output_realisasi' => 'nullable|string',
            'file_input' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
            'file_output' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
        ]);
        $bagian = Bagian::findOrFail($data['bagian_id']);
        abort_unless($user->isKepalaBps() || $user->isAdmin() ||
            (ModeKerja::ketuaAktif($user) && $user->memimpinTim($bagian->id)), 403,
            'Anda tidak dapat memberi tugas pada tim ini.');
        if (($data['sumber_judul'] ?? 'manual') === 'kamus') {
            $kamus = KamusKerja::tersediaUntuk($user)->findOrFail($data['kamus_kerja_id']);
            if ($kamus->bagian_id && (int) $kamus->bagian_id !== (int) $bagian->id) {
                throw ValidationException::withMessages(['kamus_kerja_id' => 'Entri Kamus Kerja ini hanya berlaku untuk tim pemiliknya.']);
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
                throw ValidationException::withMessages(['penerima_ids' =>
                    'Ada pelaksana yang bukan anggota tim tujuan atau Anda tidak berhak menugaskannya.']);
            }
        }
        if (!empty($data['induk_tugas_id'])) {
            $induk = AksesPekerjaan::lingkup($user)->findOrFail($data['induk_tugas_id']);
            abort_unless(AksesPekerjaan::bolehDisposisi($user, $induk),403);
            if ($induk->adalahIndukDelegasiSop()) {
                throw ValidationException::withMessages(['induk_tugas_id' => 'Gunakan menu Delegasi Langkah SOP untuk pekerjaan induk SOP.']);
            }
            if ((int) $induk->bagian_id !== (int) $bagian->id && !$user->isKepalaBps() && !$user->isAdmin()) {
                throw ValidationException::withMessages(['bagian_id' => 'Disposisi turunan harus pada tim induknya.']);
            }
        }
        if ($data['jenis_input'] === 'output_tugas') {
            if (empty($data['input_tugas_id'])) throw ValidationException::withMessages(['input_tugas_id' => 'Pilih pekerjaan sumber output.']);
            AksesPekerjaan::lingkup($user)->findOrFail($data['input_tugas_id']);
        } else {
            $data['input_tugas_id'] = null;
        }
        if ($data['jenis_input'] === 'surat' && empty($data['uraian_input'])
            && empty($data['nomor_surat']) && !$request->hasFile('file_input')) {
            throw ValidationException::withMessages(['uraian_input' => 'Isi keterangan, nomor surat, atau unggah berkas.']);
        }
        // Kolom deskripsi pada database lama masih NOT NULL. Saat proses kosong, gunakan judul.
        $data['deskripsi'] = trim($data['deskripsi'] ?? '') ?: $data['judul'];
        $data['dibuat_oleh'] = $user->id;
        $data['status'] = 'belum_dikerjakan';
        unset($data['penerima_ids']);
        $berkas = [];
        try {
            foreach (['file_input','file_output'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('pekerjaan', 'local');
                    $berkas[] = $data[$field];
                } else unset($data[$field]);
            }
            // Setiap pegawai memperoleh row tugas tersendiri; semuanya dibuat atomik.
            $pertama = DB::transaction(function () use ($ids, $data) {
                $pertama = null;
                foreach ($ids as $id) {
                    $tugas = Tugas::create([...$data, 'ditugaskan_ke' => $id]);
                    $pertama ??= $tugas;
                }
                return $pertama;
            });
        } catch (\Throwable $e) {
            foreach ($berkas as $path) Storage::disk('local')->delete($path);
            throw $e;
        }
        return redirect()->route('pekerjaan.show', $pertama)
            ->with('success', 'Pekerjaan berhasil dibuat untuk '.count($ids).' pelaksana, masing-masing memiliki progres sendiri.');
    }

    public function show(Request $request, Tugas $tugas)
    {
        abort_unless(AksesPekerjaan::dapatLihat($request->user(),$tugas),403);
        $tugas->load(['pemberiTugas','penerimaTugas','bagian','induk','inputDariTugas',
            'disposisi.penerimaTugas','disposisi.bagian','dipakaiSebagaiInput','sopLangkah.sop','sopPelaksanaan','kamusKerja',
            'laporanHarian' => fn ($q) => $q->latest()]);
        $sumberOutput = $tugas->semuaSumberOutput();
        $pemakaiOutput = $tugas->dipakaiSebagaiInput;
        if ($tugas->sop_pelaksanaan_id && $tugas->sop_langkah_id) {
            $langkah = $tugas->sopLangkah;
            $berikut = \App\Models\SopLangkah::where('sop_id', $langkah->sop_id)
                ->where('urutan', '>', $langkah->urutan)->orderBy('urutan')->first();
            if ($berikut && $berikut->sumber_input === 'sebelumnya') {
                $pemakaiOutput = $pemakaiOutput->merge(Tugas::where('sop_pelaksanaan_id', $tugas->sop_pelaksanaan_id)
                    ->where('sop_langkah_id', $berikut->id)->get())->unique('id');
            }
        }
        return view('pekerjaan.show', compact('tugas', 'sumberOutput', 'pemakaiOutput'));
    }

    public function update(Request $request, Tugas $tugas)
    {
        $user = $request->user();
        abort_unless(AksesPekerjaan::dapatLihat($user,$tugas) &&
            ($tugas->ditugaskan_ke === $user->id || $user->isKepalaBps() || $user->isAdmin()),403);
        $data = $request->validate([
            'status'=>['required',Rule::in(['belum_dikerjakan','dikerjakan','selesai'])],
            'output_realisasi'=>'nullable|string',
            'file_output'=>'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
        ]);
        if ($data['status'] !== 'belum_dikerjakan' && $tugas->jenis_input === 'output_tugas') {
            $sumber = $tugas->semuaSumberOutput();
            if ($sumber->isEmpty() || $sumber->contains(fn ($pekerjaan) => $pekerjaan->status !== 'selesai')) {
                throw ValidationException::withMessages(['status'=>
                    'Seluruh pekerjaan pada langkah sumber input harus selesai sebelum pekerjaan ini dimulai atau diselesaikan.']);
            }
        }
        if ($data['status'] === 'selesai' && !$request->filled('output_realisasi') && !$request->hasFile('file_output') && !$tugas->file_output) {
            throw ValidationException::withMessages(['output_realisasi'=>'Isi realisasi output atau unggah output sebelum selesai.']);
        }
        if ($data['status'] === 'selesai' && $tugas->adalahIndukDelegasiSop()) {
            $pelaksanaan = $tugas->sopPelaksanaan;
            $jumlahLangkah = $pelaksanaan->sop->langkah()->count();
            $jumlahDisposisi = $pelaksanaan->tugas()->whereNotNull('sop_langkah_id')
                ->distinct()->count('sop_langkah_id');
            if ($jumlahDisposisi < $jumlahLangkah) {
                throw ValidationException::withMessages(['status'=>'Belum semua langkah SOP mempunyai pelaksana.']);
            }
        }
        if ($data['status'] === 'selesai' && $tugas->disposisi()->where('status','!=','selesai')->exists()) {
            throw ValidationException::withMessages(['status'=>'Masih ada disposisi bawahan yang belum selesai.']);
        }
        if ($request->hasFile('file_output')) {
            $data['file_output'] = $request->file('file_output')->store('pekerjaan','local');
        }
        $tugas->update($data);
        return back()->with('success','Progres dan output pekerjaan tersimpan.');
    }

    /** Revisi rincian tugas setelah penugasan, tanpa merusak hubungan IPO/SOP dan audit progres. */
    private function bolehEditRincian(Request $request, Tugas $tugas): void
    {
        $user = $request->user();
        $boleh = $user->isAdmin() || $user->isKepalaBps()
            || (ModeKerja::ketuaAktif($user) && $user->memimpinTim($tugas->bagian_id)
                && (int) $tugas->dibuat_oleh === (int) $user->id)
            || (int) $tugas->ditugaskan_ke === (int) $user->id;
        abort_unless($boleh && AksesPekerjaan::dapatLihat($user, $tugas), 403);
        abort_if($tugas->status === 'selesai', 409,
            'Pekerjaan selesai tidak bisa diedit langsung. Hubungi penanggung jawab untuk revisi status.');
    }

    public function editRincian(Request $request, Tugas $tugas)
    {
        $this->bolehEditRincian($request, $tugas);
        $tugas->load(['bagian', 'sopLangkah.sop', 'penerimaTugas']);
        return view('pekerjaan.edit-rincian', compact('tugas'));
    }

    public function updateRincian(Request $request, Tugas $tugas)
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
        // Identitas SOP, tim, pelaksana dan sumber output tidak dapat dimanipulasi.
        $tugas->update($data);
        return redirect()->route('pekerjaan.show', $tugas)
            ->with('success', 'Rincian Input–Proses–Output dan jadwal pekerjaan diperbarui.');
    }

    public function unduh(Request $request, Tugas $tugas, string $jenis)
    {
        abort_unless(in_array($jenis,['input','output'],true),404);
        // Penerima pekerjaan turunan juga berhak membuka berkas output sumber inputnya.
        $boleh = AksesPekerjaan::dapatLihat($request->user(), $tugas);
        if (!$boleh && $jenis === 'output') {
            $boleh = Tugas::where('input_tugas_id', $tugas->id)
                ->whereIn('id', AksesPekerjaan::lingkup($request->user())->select('id'))
                ->exists();
            // Langkah berikutnya dapat memakai output dari SEMUA pelaksana langkah sebelumnya.
            if (!$boleh && $tugas->sop_pelaksanaan_id && $tugas->sop_langkah_id) {
                $langkah = $tugas->sopLangkah;
                $berikut = \App\Models\SopLangkah::where('sop_id', $langkah->sop_id)
                    ->where('urutan', '>', $langkah->urutan)->orderBy('urutan')->first();
                if ($berikut && $berikut->sumber_input === 'sebelumnya') {
                    $boleh = AksesPekerjaan::lingkup($request->user())
                        ->where('sop_pelaksanaan_id', $tugas->sop_pelaksanaan_id)
                        ->where('sop_langkah_id', $berikut->id)
                        ->exists();
                }
            }
        }
        abort_unless($boleh,403);
        $path = $jenis === 'input' ? $tugas->file_input : $tugas->file_output;
        abort_unless($path && Storage::disk('local')->exists($path),404);
        return Storage::disk('local')->download($path);
    }
}
