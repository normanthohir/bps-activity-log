<?php

namespace App\Http\Controllers\Staf;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    // Riwayat laporan milik user yang login
    public function index(Request $request): View
    {
        $query = LaporanHarian::with('tugas')
            ->where('user_id', $request->user()->id);   // pakai query dasar Anda yang sekarang

        // Cari: uraian atau judul pekerjaan
        if ($request->filled('cari')) {
            $cari = $request->input('cari');
            $query->where(function ($q) use ($cari) {
                $q->where('uraian', 'like', "%{$cari}%")
                    ->orWhereHas('tugas', fn($t) => $t->where('judul', 'like', "%{$cari}%"));
            });
        }

        // Periode "YYYY-MM" dari input type="month"
        if ($request->filled('periode') && preg_match('/^\d{4}-\d{2}$/', $request->input('periode'))) {
            [$tahun, $bulan] = explode('-', $request->input('periode'));
            $query->whereYear('tanggal', (int) $tahun)
                ->whereMonth('tanggal', (int) $bulan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $laporan = $query->latest('tanggal')->paginate(20)->withQueryString();

        return view('laporan.index', compact('laporan'));
    }

    public function show(LaporanHarian $laporan): View
    {
        $this->authorize('view', $laporan);

        $laporan->load(['tugas', 'logApproval.approver']);

        return view('laporan.show', compact('laporan'));
    }

    public function create(Request $request): View
    {
        // Pekerjaan yang sudah punya laporan dari pengguna ini
        $sudahDilaporkan = LaporanHarian::where('user_id', $request->user()->id)
            ->whereNotNull('tugas_id')
            ->select('tugas_id');

        // Hanya pekerjaan milik sendiri yang sudah selesai dan belum pernah dilaporkan
        $tugasAktif = $request->user()
            ->tugasDiterima()
            ->whereNotIn('status', ['dikerjakan', 'belum_dikerjakan'])
            ->whereNotIn('tugas.id', $sudahDilaporkan)
            ->get();

        $tugasTerpilih = $tugasAktif->firstWhere('id', $request->integer('tugas'));

        return view('laporan.create', compact('tugasAktif', 'tugasTerpilih'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['required', 'in:selesai,gagal,tertunda'],
            'alasan' => ['nullable', 'string'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', Rule::exists('tugas', 'id')->where('ditugaskan_ke', auth()->id())],
            'file_lampiran' => ['nullable', 'url'],
            // tombol submit menentukan status: draft atau langsung diajukan
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);

        if (!empty($data['tugas_id'])) {
            $sudahAda = LaporanHarian::where('user_id', $request->user()->id)
                ->where('tugas_id', $data['tugas_id'])
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'tugas_id' => 'Pekerjaan ini sudah pernah dilaporkan.',
                ]);
            }
        }
        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan = LaporanHarian::create($data);

        // Kalau laporan ini terkait sebuah tugas, dan tugasnya masih
        // berstatus "belum_dikerjakan", otomatis update jadi "dikerjakan"
        // supaya kepala bagian/kepala BPS tahu progresnya sudah mulai jalan.
        if ($laporan->tugas_id) {
            Tugas::where('id', $laporan->tugas_id)
                ->where('status', 'belum_dikerjakan')
                ->update(['status' => 'dikerjakan']);
        }

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    public function edit(LaporanHarian $laporan): View
    {
        $this->authorize('update', $laporan);

        // Laporan lain milik pengguna (selain yang sedang diedit)
        $sudahDilaporkan = LaporanHarian::where('user_id', auth()->id())
            ->whereNotNull('tugas_id')
            ->where('id', '!=', $laporan->id)
            ->select('tugas_id');

        $tugasAktif = auth()->user()->tugasDiterima()
            ->where(function ($q) use ($laporan, $sudahDilaporkan) {
                $q->where('tugas.id', $laporan->tugas_id)          // pekerjaan laporan ini sendiri
                    ->orWhere(fn($s) => $s->where('status', 'selesai')
                        ->whereNotIn('tugas.id', $sudahDilaporkan));
            })
            ->get();

        return view('laporan.edit', compact('laporan', 'tugasAktif'));
    }

    public function update(Request $request, LaporanHarian $laporan): RedirectResponse
    {
        $this->authorize('update', $laporan);

        $data = $request->validate([
            'tanggal' => ['required', 'date', function ($atribut, $nilai, $gagal) use ($laporan) {
                $asli = $laporan->tanggal->toDateString();
                $nilaiTanggal = \Carbon\Carbon::parse($nilai)->toDateString();

                if ($nilaiTanggal !== $asli && $nilaiTanggal < today()->toDateString()) {
                    $gagal('Tanggal tidak boleh sebelum hari ini.');
                }
            }],
            'jam_mulai' => ['nullable', 'date_format:H:i,H:i:s'],
            'jam_selesai' => ['nullable', 'date_format:H:i,H:i:s', 'after:jam_mulai'],
            'uraian' => ['required', 'string'],
            'output' => ['required', 'in:selesai,gagal,tertunda'],
            'alasan' => ['required_if:output,gagal,tertunda', 'nullable', 'string', 'max:1000'],
            'lokasi' => ['required', 'in:kantor,lapangan,dinas_luar'],
            'tugas_id' => ['nullable', Rule::exists('tugas', 'id')->where('ditugaskan_ke', auth()->id())],
            'file_lampiran' => ['nullable', 'url'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ], [
            'alasan.required_if' => 'Alasan wajib diisi jika output gagal atau tertunda.',
        ]);

        // Alasan hanya berlaku untuk output gagal/tertunda. Ditulis eksplisit
        // supaya nilai lama pasti terhapus saat output diganti ke "selesai",
        // dan pasti terisi saat output menjadi gagal/tertunda.
        $data['alasan'] = $data['output'] === 'selesai'
            ? null
            : trim((string) ($data['alasan'] ?? ''));

        $data['status'] = $data['aksi'] === 'ajukan' ? 'menunggu' : 'draft';
        unset($data['aksi']);

        $laporan->update($data);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(LaporanHarian $laporan): RedirectResponse
    {
        $this->authorize('delete', $laporan);

        $laporan->delete();

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }
}
