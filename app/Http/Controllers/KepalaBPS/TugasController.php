<?php

namespace App\Http\Controllers\KepalaBPS;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
{
      public function index(Request $request): View
    {
        $tugas = Tugas::where('dibuat_oleh', $request->user()->id)
            ->with(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
                $q->latest();
            }])
            ->when($request->filled('cari'), function ($q) use ($request) {
                $keyword = $request->string('cari');
                $q->where(function ($q) use ($keyword) {
                    $q->where('judul', 'like', "%{$keyword}%")
                        ->orWhereHas('penerimaTugas', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->when($request->filled('bagian'), function ($q) use ($request) {
                $q->where('bagian_id', $request->integer('bagian'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $daftarBagian = Bagian::orderBy('kode_bagian')->orderBy('nama_bagian')->get();

        return view('tugas.index-kepala-bps', compact('tugas', 'daftarBagian'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('pekerjaan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bagian_id' => ['required', 'exists:bagian,id'],
            'ditugaskan_ke' => ['required', 'exists:users,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $penerima = User::findOrFail($data['ditugaskan_ke']);
        abort_unless(\App\Support\AksesPekerjaan::tujuanValid($request->user(), $penerima, Bagian::findOrFail($data['bagian_id'])), 403,
            'Pegawai yang dipilih bukan bagian dari bagian tersebut.');

        Tugas::create([
            'dibuat_oleh' => $request->user()->id,
            'ditugaskan_ke' => $penerima->id,
            'bagian_id' => $data['bagian_id'],
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'tenggat' => $data['tenggat'] ?? null,
            'status' => 'belum_dikerjakan',
        ]);

        return redirect()->route('kepala-bps.tugas.index')
            ->with('success', 'Tugas berhasil diberikan kepada ' . $penerima->name . '.');
    }

    public function show(Request $request, Tugas $tugas): View
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        $tugas->load(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
            $q->latest();
        }]);

        return view('tugas.show-kepala-bps', compact('tugas'));
    }

    public function edit(Request $request, Tugas $tugas): View
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        return view('tugas.edit-kepala-bps', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['nullable', 'date'],
        ]);

        $tugas->update($data);

        return redirect()->route('kepala-bps.tugas.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBps(), 403);

        abort_if($tugas->sop_pelaksanaan_id !== null, 422, 'Pekerjaan yang terhubung ke pelaksanaan SOP tidak dapat dihapus dari menu tugas lama.');
        abort_if($tugas->disposisi()->exists() || $tugas->dipakaiSebagaiInput()->exists(), 422, 'Tugas terhubung dengan disposisi atau digunakan oleh pekerjaan lain.');
        $tugas->delete();

        return redirect()->route('kepala-bps.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
}
