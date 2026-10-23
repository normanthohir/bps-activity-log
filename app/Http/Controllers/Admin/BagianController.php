<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BagianController extends Controller
{
    public function index(Request $request): View
    {
        $bagian = Bagian::with('kepalaBagian')
            ->withCount('pegawai')
            ->when($request->filled('search'), function ($q) use ($request) {
                $kata = trim($request->search);
                $q->where(function ($q) use ($kata) {
                    $q->where('nama_bagian', 'like', "%{$kata}%")
                        ->orWhere('kode_bagian', 'like', "%{$kata}%");
                });
            })
            ->when($request->status === 'ada', fn($q) => $q->whereNotNull('kepala_bagian_id'))
            ->when($request->status === 'belum', fn($q) => $q->whereNull('kepala_bagian_id'))
            ->orderBy('nama_bagian')
            ->paginate(25)
            ->withQueryString();

        // Ringkasan dari semua bagian, bukan dari hasil filter
        $ringkasan = [
            'total'        => Bagian::count(),
            'pegawai'      => User::whereNotNull('bagian_id')->count(),
            'belum_kepala' => Bagian::whereNull('kepala_bagian_id')->count(),
        ];

        return view('admin.bagian.index', compact('bagian', 'ringkasan'));
    }

    public function create(): View
    {
        return view('admin.bagian.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_bagian' => ['required', 'string', 'max:255'],
            'kode_bagian' => ['required', 'string', 'max:50', 'unique:bagian,kode_bagian'],
        ]);

        Bagian::create($data);

        return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil ditambahkan.');
    }

    public function edit(Bagian $bagian): View
    {
        // Kepala bagian hanya boleh dipilih dari pegawai yang memang ada di bagian ini
        $calonKepala = User::where('bagian_id', $bagian->id)->get();

        return view('admin.bagian.edit', compact('bagian', 'calonKepala'));
    }

    public function update(Request $request, Bagian $bagian): RedirectResponse
    {
        $data = $request->validate([
            'nama_bagian' => ['required', 'string', 'max:255'],
            'kode_bagian' => ['required', 'string', 'max:50', 'unique:bagian,kode_bagian,' . $bagian->id],
            'kepala_bagian_id' => ['nullable', 'exists:users,id'],
        ]);

        $bagian->update($data);

        return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil diperbarui.');
    }

    public function destroy(Bagian $bagian): RedirectResponse
    {
        abort_if($bagian->pegawai()->exists(), 403, 'Tidak bisa hapus bagian yang masih punya pegawai.');

        $bagian->delete();

        return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil dihapus.');
    }
}
