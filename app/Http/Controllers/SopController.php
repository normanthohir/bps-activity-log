<?php
namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\SopPelaksanaan;
use App\Models\Tugas;
use App\Models\User;
use App\Support\AksesPekerjaan;
use App\Support\ModeKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $sops = Sop::with(['bagian', 'pembuat', 'pelaksanaan' => function ($q) use ($user) {
            // Hanya pelaksanaan yang benar-benar ditugaskan kepada pegawai login.
            $q->whereHas('tugasInduk', fn ($t) => $t->where('ditugaskan_ke', $user->id))
                ->with('tugasInduk')->latest();
        }])->withCount('langkah')
            ->when(!$user->isAdmin() && !$user->isKepalaBps(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->whereNull('bagian_id') // SOP kantor
                        ->orWhere('dibuat_oleh', $user->id)
                        ->orWhereIn('bagian_id', $user->timDipimpinIds())
                        ->orWhereHas('pelaksanaan', fn ($p) => $p->whereHas('tugasInduk',
                            fn ($t) => $t->where('ditugaskan_ke', $user->id)));
                });
            })->orderBy('kode')->paginate(15);
        $timPilihanImport = ModeKerja::ketuaAktif($user) ? AksesPekerjaan::timYangBolehDitugaskan($user) : collect();
        return view('sop.index', compact('sops', 'timPilihanImport'));
    }

    private function bolehAtur(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()), 403,
            'SOP hanya dibuat oleh Admin atau Ketua Tim dalam mode Ketua.');
    }

    private function bolehUbah(Request $request,Sop $sop): void
    {
        $this->bolehAtur($request);
        abort_unless($request->user()->isAdmin() || (
            (int) $sop->dibuat_oleh === (int) $request->user()->id
            && (!$sop->bagian_id || $request->user()->memimpinTim($sop->bagian_id))
        ), 403);
    }

    public function create(Request $request)
    {
        $this->bolehAtur($request);
        return view('sop.form', ['sop'=>null, 'timDipimpin' => AksesPekerjaan::timYangBolehDitugaskan($request->user())]);
    }

    private function validasi(Request $request, ?Sop $sop = null): array
    {
        return $request->validate([
            'kode'=>['required','string','max:100', Rule::unique('sop','kode')->ignore($sop?->id)],
            'nama'=>'required|string|max:255', 'versi'=>'required|string|max:50',
            'keterangan'=>'nullable|string',
            'bagian_id' => $request->user()->isAdmin() ? 'nullable|integer' : 'required|integer|exists:bagian,id',
            'langkah'=>'required|array|min:1|max:50',
            'langkah.*.proses'=>'required|string',
            'langkah.*.input'=>'nullable|string',
            'langkah.*.output'=>'nullable|string',
            'langkah.*.durasi_hari'=>'required|integer|min:0|max:3650',
            'langkah.*.sumber_input'=>['required',Rule::in(['manual','surat','sebelumnya'])],
        ]);
    }

    private function simpanLangkah(Sop $sop,array $langkah): void
    {
        foreach (array_values($langkah) as $n=>$item) {
            $sop->langkah()->create([...$item,'urutan'=>$n+1]);
        }
    }

    public function store(Request $request)
    {
        $this->bolehAtur($request);
        $data = $this->validasi($request);
        if ($request->user()->isAdmin()) $data['bagian_id'] = null;
        else abort_unless($request->user()->memimpinTim((int) $data['bagian_id']), 403,
            'SOP harus menggunakan tim yang Anda pimpin.');
        $sop = DB::transaction(function () use ($data,$request) {
            $sop = Sop::create([...collect($data)->except('langkah')->all(),'dibuat_oleh'=>$request->user()->id]);
            $this->simpanLangkah($sop,$data['langkah']);
            return $sop;
        });
        return redirect()->route('sop.show',$sop)->with('success','SOP tersimpan.');
    }

    public function show(Sop $sop)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isKepalaBps() && $sop->bagian_id !== null) {
            abort_unless($user->tim()->where('bagian.id', $sop->bagian_id)->exists()
                || (int) $sop->dibuat_oleh === (int) $user->id, 403);
        }
        $sop->load(['langkah', 'bagian', 'pembuat']);
        $user = auth()->user();
        $pelaksanaanDiterima = $sop->pelaksanaan()->whereHas('tugasInduk',
            fn ($q) => $q->where('ditugaskan_ke', $user->id)
                ->whereIn('bagian_id', ModeKerja::timDipimpinAktifIds($user)))
            ->with('tugasInduk.bagian')->latest()->get();
        $bolehMulai = ModeKerja::ketuaAktif($user) && (int) $sop->dibuat_oleh === (int) $user->id
            && $sop->bagian_id && $user->memimpinTim($sop->bagian_id);
        return view('sop.show', compact('sop', 'pelaksanaanDiterima', 'bolehMulai'));
    }

    public function edit(Request $request, Sop $sop)
    {
        $this->bolehUbah($request,$sop);
        $sop->load('langkah');
        $timDipimpin = AksesPekerjaan::timYangBolehDitugaskan($request->user());
        return view('sop.form', compact('sop', 'timDipimpin'));
    }

    public function update(Request $request, Sop $sop)
    {
        $this->bolehUbah($request,$sop);
        if ($sop->pelaksanaan()->exists() || \App\Models\Tugas::whereHas('sopLangkah', fn ($q) => $q->where('sop_id',$sop->id))->exists()) {
            throw ValidationException::withMessages(['kode'=>'SOP sudah digunakan untuk pekerjaan. Buat SOP versi/kode baru agar langkah pada pekerjaan lama tidak berubah.']);
        }
        $data = $this->validasi($request,$sop);
        if ($request->user()->isAdmin()) $data['bagian_id'] = $sop->bagian_id;
        else abort_unless($request->user()->memimpinTim((int) $data['bagian_id']), 403);
        DB::transaction(function () use ($sop,$data) {
            $sop->update(collect($data)->except('langkah')->all());
            // Referensi ke langkah dari pekerjaan historis dibuat nullable: aman untuk revisi.
            \App\Models\SopLangkah::where('sop_id',$sop->id)->delete();
            $this->simpanLangkah($sop,$data['langkah']);
        });
        return redirect()->route('sop.show',$sop)->with('success','SOP direvisi. Pekerjaan historis tetap tersimpan.');
    }

    public function jalankanForm(Request $request,Sop $sop)
    {
        $this->bolehAtur($request);
        $sop->load('langkah');
        $bagian = AksesPekerjaan::timYangBolehDitugaskan($request->user());
        $penerima = User::whereIn('role',['staf','kepala_bagian'])->orderBy('name')->get();
        return view('sop.jalankan',compact('sop','bagian','penerima'));
    }

    public function jalankan(Request $request,Sop $sop)
    {
        $this->bolehAtur($request);
        $sop->load('langkah');
        $data = $request->validate([
            'nama'=>'required|string|max:255',
            'tanggal_mulai'=>'required|date',
            'uraian_input'=>'nullable|string',
            'asal_surat'=>'nullable|string|max:255',
            'nomor_surat'=>'nullable|string|max:255',
            'tanggal_surat'=>'nullable|date',
            'file_input'=>'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
            'petugas'=>'required|array',
            'petugas.*.bagian_id'=>'required|integer|exists:bagian,id',
            'petugas.*.ditugaskan_ke'=>'required|integer|exists:users,id',
        ]);
        foreach ($sop->langkah as $langkah) {
            $item = $data['petugas'][$langkah->id] ?? null;
            if (!$item) throw ValidationException::withMessages(['petugas'=>'Semua langkah harus memiliki tim dan pelaksana.']);
            abort_unless(AksesPekerjaan::tujuanValid($request->user(),User::findOrFail($item['ditugaskan_ke']),Bagian::findOrFail($item['bagian_id'])),403);
        }
        $file = $request->hasFile('file_input') ? $request->file('file_input')->store('pekerjaan','local') : null;
        $pelaksanaan = DB::transaction(function () use ($request,$data,$sop,$file) {
            $pelaksanaan = SopPelaksanaan::create(['sop_id'=>$sop->id,'nama'=>$data['nama'],'dibuat_oleh'=>$request->user()->id]);
            $sebelumnya = null;
            $tanggal = \Illuminate\Support\Carbon::parse($data['tanggal_mulai']);
            foreach ($sop->langkah as $urutan=>$langkah) {
                $penugasan = $data['petugas'][$langkah->id];
                $jenis = ($urutan > 0 && $langkah->sumber_input === 'sebelumnya') ? 'output_tugas' :
                    ($urutan === 0 && $langkah->sumber_input === 'sebelumnya' ? 'manual' : $langkah->sumber_input);
                $tugas = Tugas::create([
                    'sop_pelaksanaan_id'=>$pelaksanaan->id,'sop_langkah_id'=>$langkah->id,
                    'dibuat_oleh'=>$request->user()->id,
                    'ditugaskan_ke'=>$penugasan['ditugaskan_ke'], 'bagian_id'=>$penugasan['bagian_id'],
                    'judul'=>$data['nama'].' — '.$langkah->urutan.'. '.\Illuminate\Support\Str::limit($langkah->proses,90),
                    'deskripsi'=>$langkah->proses, 'status'=>'belum_dikerjakan',
                    'jenis_input'=>$jenis, 'input_tugas_id'=>$jenis==='output_tugas' ? $sebelumnya?->id : null,
                    'uraian_input'=> $urutan === 0 ? ($data['uraian_input'] ?? $langkah->input) : $langkah->input,
                    'asal_surat'=> $urutan === 0 ? ($data['asal_surat'] ?? null) : null,
                    'nomor_surat'=> $urutan === 0 ? ($data['nomor_surat'] ?? null) : null,
                    'tanggal_surat'=> $urutan === 0 ? ($data['tanggal_surat'] ?? null) : null,
                    'file_input'=> $urutan === 0 ? $file : null,
                    'output_rencana'=>$langkah->output, 'tanggal_mulai'=>$tanggal->toDateString(),
                    'tenggat'=>$tanggal->copy()->addDays((int)$langkah->durasi_hari)->toDateString(),
                ]);
                $tanggal = $tanggal->copy()->addDays((int)$langkah->durasi_hari);
                $sebelumnya = $tugas;
            }
            return $pelaksanaan;
        });
        return redirect()->route('sop.pelaksanaan',$pelaksanaan)->with('success','Rangkaian pekerjaan dari SOP berhasil dibuat.');
    }

    public function pelaksanaan(Request $request,SopPelaksanaan $pelaksanaan)
    {
        $pelaksanaan->load(['sop','tugas'=>fn($q) => $q->orderBy('id')]);
        $visible = $pelaksanaan->tugas->filter(fn($t) => AksesPekerjaan::dapatLihat($request->user(),$t));
        abort_if($visible->isEmpty(),403);
        return view('sop.pelaksanaan',compact('pelaksanaan','visible'));
    }
}
