<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BagianController extends Controller
{
    public function index(): View
    {
        $bagian = Bagian::withCount('anggotaTim')->with('kepalaBagian')->orderBy('kode_bagian')->orderBy('nama_bagian')->get();
        return view('admin.bagian.index', compact('bagian'));
    }

    public function create(): View
    {
        $calonKepala = User::whereIn('role', ['staf', 'kepala_bagian'])->orderBy('name')->get();
        return view('admin.bagian.create', compact('calonKepala'));
    }

    private function validasi(Request $request, ?Bagian $bagian = null): array
    {
        return $request->validate([
            'nama_bagian' => ['required', 'string', 'max:255'],
            'kode_bagian' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('bagian', 'kode_bagian')->ignore($bagian?->id)],
            'kepala_bagian_id' => ['nullable', 'integer', 'exists:users,id'],
            'anggota_ids' => ['nullable', 'array'],
            'anggota_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
    }

    private function simpanKeanggotaan(Bagian $bagian, array $data): void
    {
        $ketua = !empty($data['kepala_bagian_id']) ? User::findOrFail($data['kepala_bagian_id']) : null;
        $ids = array_values(array_unique(array_map('intval', $data['anggota_ids'] ?? [])));
        if ($ketua) $ids[] = $ketua->id;
        $ids = array_unique($ids);
        $calon = User::whereIn('id', $ids)->get();
        if ($calon->contains(fn ($u) => !$u->isStaf())) {
            throw \Illuminate\Validation\ValidationException::withMessages(['anggota_ids' => 'Anggota/ketua harus pegawai operasional (bukan admin atau Kepala BPS).']);
        }
        KeanggotaanTim::tetapkanKetua($bagian, $ketua);
        $bagian->anggotaTim()->sync(collect($ids)->mapWithKeys(fn ($id) => [
            $id => ['peran_tim' => $ketua?->id === $id ? 'ketua' : 'anggota'],
        ])->all());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        DB::transaction(function () use ($data) {
            $bagian = Bagian::create(collect($data)->only(['nama_bagian', 'kode_bagian'])->all());
            $this->simpanKeanggotaan($bagian, $data);
        });
        return redirect()->route('admin.bagian.index')->with('success', 'Tim beserta ketua dan anggotanya berhasil ditambahkan.');
    }

    public function edit(Bagian $bagian): View
    {
        $calonKepala = User::whereIn('role', ['staf', 'kepala_bagian'])->orderBy('name')->get();
        $bagian->load('anggotaTim');
        return view('admin.bagian.edit', compact('bagian', 'calonKepala'));
    }

    public function update(Request $request, Bagian $bagian): RedirectResponse
    {
        $data = $this->validasi($request, $bagian);
        DB::transaction(function () use ($bagian, $data) {
            $bagian->update(collect($data)->only(['nama_bagian', 'kode_bagian'])->all());
            $this->simpanKeanggotaan($bagian, $data);
        });
        return redirect()->route('admin.bagian.index')->with('success', 'Ketua dan anggota tim berhasil diperbarui.');
    }

    public function destroy(Bagian $bagian): RedirectResponse
    {
        abort_if($bagian->pegawai()->exists() || $bagian->anggotaTim()->exists() || $bagian->tugas()->exists(),
            403, 'Tidak bisa hapus tim yang masih memiliki anggota atau pekerjaan.');
        $bagian->delete();
        return redirect()->route('admin.bagian.index')->with('success', 'Tim berhasil dihapus.');
    }
}
