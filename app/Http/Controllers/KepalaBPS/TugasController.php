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
    // Daftar semua tugas yang pernah diberikan oleh kepala BPS ini
    public function index(Request $request): View
    {
        $tugas = Tugas::where('dibuat_oleh', $request->user()->id)
            ->with(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
                $q->latest();
            }])
            ->latest()
            ->paginate(15);

        return view('tugas.index-kepala-bps', compact('tugas'));
    }

    public function create(): View
    {
        $daftarBagian = Bagian::with(['pegawai' => function ($query) {
            $query->whereIn('role', ['staf', 'kepala_bagian'])->select('id', 'name', 'role', 'bagian_id');
        }])->get(['id', 'nama_bagian']);

        $dataPegawaiPerBagian = [];
        foreach ($daftarBagian as $bagian) {
            $dataPegawaiPerBagian[$bagian->id] = $bagian->pegawai->map(function ($p) {
                return [
                    'id' => $p->id,
                    'label' => $p->name . ' (' . ($p->role === 'kepala_bagian' ? 'Kepala Bagian' : 'Staf') . ')',
                ];
            })->values();
        }

        return view('tugas.create-kepala-bps', compact('daftarBagian', 'dataPegawaiPerBagian'));
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
        abort_unless($penerima->bagian_id === (int) $data['bagian_id'], 403,
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

    // Detail satu tugas beserta riwayat laporan yang terkait
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
    
        $tugas->delete();
    
        return redirect()->route('kepala-bps.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
}