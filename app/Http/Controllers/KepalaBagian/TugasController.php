<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
{
    // Semua tugas yang pernah dibuat oleh kepala bagian ini
    // Semua tugas yang pernah dibuat oleh kepala bagian ini
    public function index(Request $request): View
    {
        $tugas = $request->user()->tugasDibuat()
            ->whereIn('bagian_id', \App\Support\ModeKerja::timDipimpinAktifIds($request->user()))
            ->with('penerimaTugas')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $keyword = $request->string('cari');
                $q->where(function ($q) use ($keyword) {
                    $q->where('judul', 'like', "%{$keyword}%")
                        ->orWhereHas('penerimaTugas', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tugas.index', compact('tugas'));
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('pekerjaan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Endpoint lama: jangan gunakan users.bagian_id sebagai izin lintas tim.
        $data = $request->validate([
            'bagian_id' => ['required', 'integer', 'exists:bagian,id'],
            'ditugaskan_ke' => ['required', 'integer', 'exists:users,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['nullable', 'date'],
        ]);
        $user = $request->user();
        $tim = \App\Models\Bagian::findOrFail($data['bagian_id']);
        $penerima = \App\Models\User::findOrFail($data['ditugaskan_ke']);
        abort_unless(\App\Support\AksesPekerjaan::tujuanValid($user, $penerima, $tim), 403);
        Tugas::create([
            ...$data, 'dibuat_oleh' => $user->id, 'status' => 'belum_dikerjakan',
            'jenis_input' => 'manual',
        ]);
        return redirect()->route('kabag.tugas.index')->with('success', 'Tugas berhasil diberikan.');
    }

    public function show(Request $request, Tugas $tugas_tim): View
    {
        abort_unless($request->user()->isKepalaBagian() && $tugas_tim->dibuat_oleh === $request->user()->id && in_array((int) $tugas_tim->bagian_id, \App\Support\ModeKerja::timDipimpinAktifIds($request->user()), true), 403);

        $tugas_tim->load(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
            $q->latest();
        }]);

        return view('tugas.show-kabag-tugas-tim', ['tugas' => $tugas_tim]);
    }

    public function edit(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBagian() && $tugas->dibuat_oleh === $request->user()->id && in_array((int) $tugas->bagian_id, \App\Support\ModeKerja::timDipimpinAktifIds($request->user()), true), 403);

        return redirect()->route('pekerjaan.show', $tugas)->with('success','Buka detail pekerjaan untuk melihat dan memperbarui hasil.');
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBagian() && $tugas->dibuat_oleh === $request->user()->id && in_array((int) $tugas->bagian_id, \App\Support\ModeKerja::timDipimpinAktifIds($request->user()), true), 403);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['nullable', 'date'],
        ]);

        $tugas->update($data);

        return redirect()->route('kabag.tugas.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBagian() && $tugas->dibuat_oleh === $request->user()->id && in_array((int) $tugas->bagian_id, \App\Support\ModeKerja::timDipimpinAktifIds($request->user()), true), 403);

        abort_if($tugas->sop_pelaksanaan_id !== null, 422, 'Pekerjaan yang terhubung ke pelaksanaan SOP tidak dapat dihapus dari menu tugas lama.');
        abort_if($tugas->disposisi()->exists() || $tugas->dipakaiSebagaiInput()->exists(), 422, 'Tugas terhubung dengan disposisi atau digunakan oleh pekerjaan lain.');
        $tugas->delete();

        return redirect()->route('kabag.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
}
