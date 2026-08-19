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
    public function index(Request $request): View
    {
        $tugas = $request->user()->tugasDibuat()->latest()->paginate(15);

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
        abort_unless($stafTujuan->bagian_id === $user->bagian_id, 403,
            'Staf tujuan bukan bagian dari seksi Anda.');

        Tugas::create([
            ...$data,
            'dibuat_oleh' => $user->id,
            'bagian_id' => $user->bagian_id,
            'status' => 'belum_dikerjakan',
        ]);

        return redirect()->route('kabag.tugas.index')
            ->with('success', 'Tugas berhasil diberikan.');
    }
}
