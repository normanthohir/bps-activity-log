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
    // Kepala BPS melihat SEMUA tugas yang pernah dibuat (tanpa filter bagian)
    public function index(Request $request): View
    {
        $tugas = Tugas::with(['penerimaTugas', 'bagian'])
            ->latest()
            ->paginate(15);

        return view('tugas.index', compact('tugas'));
    }

    // Kepala BPS bisa menugaskan staf dari SEMUA bagian
    public function create(): View
    {
        $daftarStaf = User::where('role', 'staf')
            ->with('bagian')
            ->get();

        $daftarBagian = Bagian::all();

        return view('tugas.create', compact('daftarStaf', 'daftarBagian'));
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

        $stafTujuan = User::findOrFail($data['ditugaskan_ke']);

        Tugas::create([
            ...$data,
            'dibuat_oleh' => $user->id,
            'bagian_id' => $stafTujuan->bagian_id,
            'status' => 'belum_dikerjakan',
        ]);

        return redirect()->route('kepala-bps.tugas.index')
            ->with('success', 'Tugas berhasil diberikan ke ' . $stafTujuan->name . '.');
    }
}
