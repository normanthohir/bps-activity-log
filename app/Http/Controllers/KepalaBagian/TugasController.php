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

    public function create(Request $request): View
    {
        // Hanya staf DI BAGIAN yang sama dengan kepala bagian yang login
        $stafBagian = $request->user()->bagian->pegawai()->where('role', 'staf')->get();

        return view('tugas.create', compact('stafBagian'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'ditugaskan_ke' => ['required', 'exists:users,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        // Validasi keamanan tambahan: pastikan staf tujuan
        // benar-benar berada di bagian yang sama dengan kepala bagian ini.
        // Ini penting supaya Kepala Bagian A tidak bisa menugaskan
        // staf di Bagian B lewat manipulasi form.
        $stafTujuan = \App\Models\User::findOrFail($data['ditugaskan_ke']);
        abort_unless(
            $stafTujuan->bagian_id === $user->bagian_id,
            403,
            'Staf tujuan bukan bagian dari seksi Anda.'
        );

        Tugas::create([
            ...$data,
            'dibuat_oleh' => $user->id,
            'bagian_id' => $user->bagian_id,
            'status' => 'belum_dikerjakan',
        ]);
        
        return redirect()->route('kabag.tugas.index')->with('success', 'Tugas berhasil diberikan');
    }
    public function show(Request $request, Tugas $tugas_tim): View
    {
        abort_unless($request->user()->isKepalaBagian(), 403);

        $tugas_tim->load(['penerimaTugas', 'bagian', 'laporanHarian' => function ($q) {
            $q->latest();
        }]);

        return view('tugas.show-kabag-tugas-tim', ['tugas' => $tugas_tim]);
    }

    public function edit(Request $request, Tugas $tugas): View
    {
        abort_unless($request->user()->isKepalaBagian(), 403);

        return view('tugas.edit-kabag-tugas-tim', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($request->user()->isKepalaBagian(), 403);

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
        abort_unless($request->user()->isKepalaBagian(), 403);

        $tugas->delete();

        return redirect()->route('kabag.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
}
