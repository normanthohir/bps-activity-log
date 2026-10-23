<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\KamusKerja;
use App\Support\ModeKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KamusKerjaController extends Controller
{
    private function bolehKelola(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()), 403);
    }

    private function timPilihan(Request $request)
    {
        return $request->user()->isAdmin() ? Bagian::orderBy('kode_bagian')->get()
            : $request->user()->timDipimpin()->orderBy('kode_bagian')->get();
    }

    private function bolehUbah(Request $request, KamusKerja $kamus): void
    {
        $this->bolehKelola($request);
        if ($request->user()->isAdmin()) return;
        abort_unless($kamus->bagian_id && $request->user()->memimpinTim($kamus->bagian_id)
            && $kamus->dibuat_oleh === $request->user()->id, 403);
    }

    public function index(Request $request)
    {
        $this->bolehKelola($request);
        $kamus = KamusKerja::tersediaUntuk($request->user())->with('bagian')
            ->when($request->filled('cari'), fn ($q) => $q->where('nama', 'like', '%'.$request->input('cari').'%'))
            ->orderBy('nama')->paginate(20)->withQueryString();
        $timPilihan = $this->timPilihan($request);
        return view('kamus.index', compact('kamus', 'timPilihan'));
    }

    public function store(Request $request)
    {
        $this->bolehKelola($request);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'input' => 'nullable|string', 'proses' => 'nullable|string', 'output' => 'nullable|string',
            'bagian_id' => 'nullable|integer|exists:bagian,id',
        ]);
        $this->periksaTim($request, $data['bagian_id'] ?? null);
        $this->periksaDuplikat($data['nama'], $data['bagian_id'] ?? null);
        KamusKerja::create([...$data, 'dibuat_oleh' => $request->user()->id]);
        return redirect()->route('kamus.index')->with('success', 'Entri Kamus Kerja ditambahkan.');
    }

    private function periksaTim(Request $request, ?int $bagianId): void
    {
        if ($request->user()->isAdmin()) {
            if ($bagianId) throw ValidationException::withMessages(['bagian_id' => 'Admin membuat Kamus Kerja kantor; tidak perlu memilih tim.']);
            return;
        }
        if (!$bagianId || !$request->user()->memimpinTim($bagianId)) {
            throw ValidationException::withMessages(['bagian_id' => 'Ketua Tim harus memilih salah satu tim yang dipimpinnya.']);
        }
    }

    private function periksaDuplikat(string $nama, ?int $bagianId, ?int $exceptId = null): void
    {
        $q = KamusKerja::where('nama', trim($nama));
        $bagianId === null ? $q->whereNull('bagian_id') : $q->where('bagian_id', $bagianId);
        if ($exceptId) $q->where('id', '!=', $exceptId);
        if ($q->exists()) throw ValidationException::withMessages(['nama' => 'Nama ini sudah tersedia dalam Kamus Kerja untuk lingkup tersebut.']);
    }

    public function edit(Request $request, KamusKerja $kamus)
    {
        $this->bolehUbah($request, $kamus);
        return view('kamus.edit', ['kamus' => $kamus, 'timPilihan' => $this->timPilihan($request)]);
    }

    public function update(Request $request, KamusKerja $kamus)
    {
        $this->bolehUbah($request, $kamus);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'input' => 'nullable|string', 'proses' => 'nullable|string', 'output' => 'nullable|string',
        ]);
        $this->periksaDuplikat($data['nama'], $kamus->bagian_id, $kamus->id);
        $kamus->update($data);
        return redirect()->route('kamus.index')->with('success', 'Kamus Kerja diperbarui.');
    }

    public function destroy(Request $request, KamusKerja $kamus)
    {
        $this->bolehUbah($request, $kamus);
        $kamus->delete(); // FK nullOnDelete, pekerjaan lama tidak ikut terhapus
        return redirect()->route('kamus.index')->with('success', 'Entri kamus dihapus; pekerjaan lama tetap ada.');
    }
}
