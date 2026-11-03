<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BagianController extends Controller
{
    /** Hanya akun dengan peran ini yang bisa menjadi ketua atau anggota tim. */
    private const PERAN_PEGAWAI = ['staf', 'kepala_bagian'];

    /* ======================================================================
     |  HALAMAN
     * ====================================================================== */

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
        return view('admin.bagian.create', [
            'calonKetua' => $this->daftarCalonKetua(),
            'calonAnggota' => $this->daftarCalonAnggota(),
        ]);
    }

    public function edit(Bagian $bagian): View
    {
        return view('admin.bagian.edit', [
            'bagian' => $bagian,
            'calonKetua' => $this->daftarCalonKetua(),
            'calonAnggota' => $this->daftarCalonAnggota($bagian),
            // Anggota yang sekarang, supaya kotaknya sudah tercentang
            'anggotaTerpilih' => $this->idAnggota($bagian)->all(),
        ]);
    }

    /* ======================================================================
     |  SIMPAN
     * ====================================================================== */

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        $anggotaIds = $this->periksaAnggota($data);

        DB::transaction(fn() => $this->simpanTim(new Bagian(), $data, $anggotaIds));

        return redirect()->route('admin.bagian.index')->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Bagian $bagian): RedirectResponse
    {
        $data = $this->validasi($request, $bagian);
        $anggotaIds = $this->periksaAnggota($data, $bagian);

        DB::transaction(fn() => $this->simpanTim($bagian, $data, $anggotaIds));

        return redirect()->route('admin.bagian.index')->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Bagian $bagian): RedirectResponse
    {
        // Ketua juga dihitung anggota, jadi tim yang masih punya ketua tidak bisa dihapus
        if ($this->idAnggota($bagian)->isNotEmpty()) {
            return redirect()->route('admin.bagian.index')
                ->with('error', "Tim {$bagian->nama_bagian} masih memiliki ketua atau anggota. Kosongkan lewat Edit Tim terlebih dahulu.");
        }

        $bagian->delete();

        return redirect()->route('admin.bagian.index')->with('success', 'Tim berhasil dihapus.');
    }

    /* ======================================================================
     |  VALIDASI
     * ====================================================================== */

    private function validasi(Request $request, ?Bagian $bagian = null): array
    {
        // Rapikan dulu: spasi dibuang, kode tim dijadikan huruf besar
        $request->merge([
            'nama_bagian' => trim((string) $request->input('nama_bagian')),
            'kode_bagian' => strtoupper(trim((string) $request->input('kode_bagian'))),
        ]);

        $pegawai = Rule::exists('users', 'id')->whereIn('role', self::PERAN_PEGAWAI);

        return $request->validate([
            'nama_bagian' => ['required', 'string', 'max:255', Rule::unique('bagian', 'nama_bagian')->ignore($bagian?->id)],
            'kode_bagian' => ['required', 'string', 'max:50', Rule::unique('bagian', 'kode_bagian')->ignore($bagian?->id)],
            'kepala_bagian_id' => ['nullable', $pegawai],
            'anggota_ids' => ['nullable', 'array'],
            'anggota_ids.*' => ['integer', $pegawai],
        ], [
            'nama_bagian.unique' => 'Nama tim sudah dipakai tim lain.',
            'kode_bagian.unique' => 'Kode tim sudah dipakai tim lain.',
            'kepala_bagian_id.exists' => 'Ketua tim yang dipilih tidak valid.',
            'anggota_ids.*.exists' => 'Ada anggota yang dipilih tidak valid.',
        ], [
            'nama_bagian' => 'nama tim',
            'kode_bagian' => 'kode tim',
            'kepala_bagian_id' => 'ketua tim',
            'anggota_ids' => 'anggota tim',
        ]);
    }

    /**
     * Anggota hanya boleh dipilih dari pegawai yang belum punya tim
     * (saat edit: ditambah anggota tim ini sendiri). Ketua dikecualikan
     * karena ketua boleh berasal dari tim lain.
     *
     * @return Collection<int,int> daftar id anggota yang sudah bersih
     */
    private function periksaAnggota(array $data, ?Bagian $bagian = null): Collection
    {
        $dipilih = collect($data['anggota_ids'] ?? [])->map(fn($id) => (int) $id)->unique()->values();
        $ketuaId = (int) ($data['kepala_bagian_id'] ?? 0);
        $yangBoleh = $this->daftarCalonAnggota($bagian)->pluck('id');

        $ditolak = $dipilih->diff($yangBoleh)->reject(fn($id) => $id === $ketuaId);

        if ($ditolak->isNotEmpty()) {
            $nama = User::whereIn('id', $ditolak)->pluck('name')->implode(', ');

            throw ValidationException::withMessages([
                'anggota_ids' => "{$nama} sudah tergabung di tim lain. Pindahkan melalui Kelola Akun Pegawai.",
            ]);
        }

        return $dipilih;
    }

    /* ======================================================================
     |  DAFTAR PILIHAN UNTUK FORM
     * ====================================================================== */

    /** Calon ketua: semua pegawai, karena satu orang boleh memimpin banyak tim. */
    private function daftarCalonKetua(): Collection
    {
        return User::with('bagian:id,nama_bagian')
            ->whereIn('role', self::PERAN_PEGAWAI)
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'bagian_id']);
    }

    /**
     * Calon anggota: pegawai yang belum tergabung di tim mana pun.
     * Saat edit, anggota tim ini sendiri ikut ditampilkan supaya bisa dilepas.
     */
    private function daftarCalonAnggota(?Bagian $bagian = null): Collection
    {
        $tanpaTim = User::whereIn('role', self::PERAN_PEGAWAI)
            ->whereNull('bagian_id')
            ->whereNotIn('id', DB::table('bagian_user')->select('user_id'))
            ->pluck('id');

        $anggotaTimIni = $bagian ? $this->idAnggota($bagian) : collect();

        return User::whereIn('id', $tanpaTim->merge($anggotaTimIni)->unique())
            ->orderBy('name')
            ->get(['id', 'name', 'nip']);
    }

    /**
     * Semua id anggota sebuah tim, termasuk ketuanya. Datanya digabung dari tiga
     * tempat karena data lama hanya memakai kolom bagian_id atau kepala_bagian_id.
     *
     * @return Collection<int,int>
     */
    private function idAnggota(Bagian $bagian): Collection
    {
        return DB::table('bagian_user')->where('bagian_id', $bagian->id)->pluck('user_id')
            ->merge(User::where('bagian_id', $bagian->id)->pluck('id'))
            ->when($bagian->kepala_bagian_id, fn($c) => $c->push($bagian->kepala_bagian_id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();
    }

    /* ======================================================================
     |  PROSES PENYIMPANAN (dipanggil dari dalam transaksi)
     * ====================================================================== */

    private function simpanTim(Bagian $bagian, array $data, Collection $anggotaIds): void
    {
        // 1. Catat keadaan SEBELUM diubah
        $ketuaLama = $bagian->kepala_bagian_id ? (int) $bagian->kepala_bagian_id : null;
        $anggotaLama = $bagian->exists ? $this->idAnggota($bagian) : collect();

        // 2. Simpan data tim
        $ketuaBaru = !empty($data['kepala_bagian_id']) ? (int) $data['kepala_bagian_id'] : null;

        $bagian->nama_bagian = $data['nama_bagian'];
        $bagian->kode_bagian = $data['kode_bagian'];
        $bagian->kepala_bagian_id = $ketuaBaru;
        $bagian->save();

        // 3. Ketua otomatis menjadi anggota
        $anggotaBaru = $anggotaIds
            ->when($ketuaBaru, fn($c) => $c->push($ketuaBaru))
            ->unique()
            ->values();

        // 4. Sinkronkan anggota dan role
        $this->sinkronAnggota($bagian, $anggotaLama, $anggotaBaru, $ketuaBaru);
        $this->sesuaikanRole($ketuaLama, $ketuaBaru);
    }

    /** Samakan tabel bagian_user dengan daftar anggota terbaru. */
    private function sinkronAnggota(Bagian $bagian, Collection $anggotaLama, Collection $anggotaBaru, ?int $ketuaId): void
    {
        // Yang dilepas: hapus dari tim ini. Bila ini tim utamanya, pindahkan ke tim lain miliknya (atau kosongkan).
        foreach ($anggotaLama->diff($anggotaBaru) as $userId) {
            DB::table('bagian_user')
                ->where('user_id', $userId)->where('bagian_id', $bagian->id)->delete();

            $timLain = DB::table('bagian_user')->where('user_id', $userId)->value('bagian_id');

            User::where('id', $userId)->where('bagian_id', $bagian->id)
                ->update(['bagian_id' => $timLain]);
        }

        // Yang dipertahankan atau baru masuk: tulis perannya (ketua atau anggota)
        foreach ($anggotaBaru as $userId) {
            DB::table('bagian_user')->updateOrInsert(
                ['user_id' => $userId, 'bagian_id' => $bagian->id],
                ['peran_tim' => $userId === $ketuaId ? 'ketua' : 'anggota'],
            );
        }

        // Pegawai yang belum punya tim utama memakai tim ini
        User::whereIn('id', $anggotaBaru)->whereNull('bagian_id')
            ->update(['bagian_id' => $bagian->id]);
    }

    /** Ketua baru menjadi Kepala Tim. Ketua lama yang tidak memimpin tim lain kembali menjadi staf. */
    private function sesuaikanRole(?int $ketuaLama, ?int $ketuaBaru): void
    {
        if ($ketuaBaru) {
            User::where('id', $ketuaBaru)->where('role', 'staf')->update(['role' => 'kepala_bagian']);
        }

        if ($ketuaLama && $ketuaLama !== $ketuaBaru
            && !Bagian::where('kepala_bagian_id', $ketuaLama)->exists()) {
            User::where('id', $ketuaLama)->where('role', 'kepala_bagian')->update(['role' => 'staf']);
        }
    }
}