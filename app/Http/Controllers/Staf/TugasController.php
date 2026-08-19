<?php

namespace App\Http\Controllers\Staf;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->tugasDiterima()->with('pemberiTugas', 'bagian');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tugas = $query->latest('tenggat')->paginate(15)->withQueryString();

        return view('staf.tugas.index', compact('tugas'));
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($tugas->ditugaskan_ke === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:belum_dikerjakan,sedang_dikerjakan,selesai'],
        ]);

        $tugas->update($data);

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }
}
