<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    
    public function index(Request $request): View
    {
        $users = User::with('bagian')
            ->when($request->filled('search'), function ($q) use ($request) {
                $kata = trim($request->search);
                $q->where(function ($q) use ($kata) {
                    $q->where('name', 'like', "%{$kata}%")
                        ->orWhere('nip', 'like', "%{$kata}%")
                        ->orWhere('email', 'like', "%{$kata}%");
                });
            })
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
            ->when($request->filled('bagian'), fn($q) => $q->where('bagian_id', $request->bagian))
            ->when($request->status === 'terverifikasi', fn($q) => $q->whereNotNull('email_verified_at'))
            ->when($request->status === 'belum', fn($q) => $q->whereNull('email_verified_at'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $ringkasan = User::selectRaw("
        COUNT(*) as total,
        SUM(role = 'staf') as staf,
        SUM(role = 'kepala_bagian') as kepala_tim,
        SUM(email_verified_at IS NULL) as belum_verifikasi
    ")->first()->toArray();

        $daftarBagian = Bagian::orderBy('nama_bagian')->get();

        return view('admin.users.index', compact('users', 'ringkasan', 'daftarBagian'));
    }

    public function create(): View
    {
        $daftarBagian = Bagian::all();
        // Untuk pilihan "atasan": ambil kepala bagian & kepala BPS yang sudah ada
        $calonAtasan = User::whereIn('role', ['kepala_bagian', 'kepala_bps'])->get();

        return view('admin.users.create', compact('daftarBagian', 'calonAtasan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());
        [$data, $tim] = $this->siapkanData($data);

        $data['password'] = Hash::make('password'); // password default
        // email_verified_at sengaja TIDAK diisi, jadi pegawai wajib verifikasi email dulu

        $user = DB::transaction(function () use ($data, $tim) {
            $user = User::create($data);
            $this->terapkanTim($user, $tim);

            return $user;
        });

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.users.index')
                ->with('warning', "Akun {$user->name} dibuat, tetapi email verifikasi gagal dikirim. Gunakan tombol \"Kirim ulang verifikasi\" di daftar user.");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Akun dibuat. Email verifikasi dikirim ke {$user->email}. Password awal: password");
    }

    public function edit(User $user): View
    {
        $daftarBagian = Bagian::orderBy('nama_bagian')->get();
        $calonAtasan = User::whereIn('role', ['kepala_bagian', 'kepala_bps'])
            ->where('id', '!=', $user->id)->get();

        // Peran saat ini di setiap tim: [bagian_id => 'anggota'|'ketua']
        $timSaatIni = DB::table('bagian_user')
            ->where('user_id', $user->id)
            ->pluck('peran_tim', 'bagian_id')
            ->map(fn($peran) => $peran === 'ketua' ? 'ketua' : 'anggota')
            ->all();

        // Tim yang tercatat dipimpin akun ini, walau baris pivotnya belum ada
        foreach (Bagian::where('kepala_bagian_id', $user->id)->pluck('id') as $id) {
            $timSaatIni[$id] = 'ketua';
        }
        // Tim utama minimal berstatus anggota
        if ($user->bagian_id && empty($timSaatIni[$user->bagian_id])) {
            $timSaatIni[$user->bagian_id] = 'anggota';
        }

        return view('admin.users.edit', compact('user', 'daftarBagian', 'calonAtasan', 'timSaatIni'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->aturan($user), $this->pesan());

        // Admin tidak boleh mencabut akses admin dari akunnya sendiri
        if ($user->id === auth()->id() && $user->isAdmin() && $data['role'] !== 'admin') {
            throw ValidationException::withMessages(['role' => 'Anda tidak bisa mengubah akses admin untuk akun sendiri.']);
        }

        [$data, $tim] = $this->siapkanData($data, $user);

        DB::transaction(function () use ($user, $data, $tim) {
            $user->update($data);
            $this->terapkanTim($user, $tim);
        });

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    /** Aturan validasi bersama untuk tambah dan ubah akun. */
    private function aturan(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', Rule::unique('users', 'nip')->ignore($user?->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            // "kepala_bagian" tidak dipilih manual: otomatis dari pilihan Ketua Tim
            'role' => ['required', 'in:staf,kepala_bps,admin'],
            'bagian_id' => ['nullable', 'required_if:role,staf', 'exists:bagian,id'],
            'atasan_id' => ['nullable', 'exists:users,id', Rule::notIn([$user?->id])],
            'tim' => ['nullable', 'array'],
            'tim.*' => ['nullable', 'in:anggota,ketua'],
        ];
    }

    private function pesan(): array
    {
        return [
            'bagian_id.required_if' => 'Tim utama wajib dipilih untuk pegawai biasa.',
            'atasan_id.not_in' => 'Atasan tidak boleh akun itu sendiri.',
            'tim.*.in' => 'Peran tim tidak valid.',
        ];
    }

    /**
     * Rapikan data hasil validasi dan periksa keanggotaan tim.
     * @return array{0: array, 1: array<int|string,string>} [data user, peran per tim]
     */
    private function siapkanData(array $data, ?User $user = null): array
    {
        $staf = $data['role'] === 'staf';

        // Keanggotaan tim hanya berlaku untuk pegawai biasa
        $tim = $staf ? collect($data['tim'] ?? [])->filter()->all() : [];
        unset($data['tim']);

        if (!$staf) {
            $data['bagian_id'] = null;
        }

        if ($tim) {
            $bagianTim = Bagian::whereIn('id', array_keys($tim))->get()->keyBy('id');

            if ($bagianTim->count() !== count($tim)) {
                throw ValidationException::withMessages(['tim' => 'Ada tim yang dipilih tidak valid.']);
            }

            // Satu tim satu ketua; penggantian ketua dilakukan lewat Edit Tim
            $bentrok = $bagianTim->filter(fn($b) => ($tim[$b->id] ?? null) === 'ketua'
                && $b->kepala_bagian_id
                && (int) $b->kepala_bagian_id !== (int) $user?->id);

            if ($bentrok->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'tim' => 'Tim ' . $bentrok->pluck('nama_bagian')->implode(', ')
                        . ' sudah memiliki ketua. Ubah melalui Edit Tim.',
                ]);
            }
        }

        // Pegawai yang memimpin minimal satu tim otomatis berperan Kepala Tim
        if (in_array('ketua', $tim, true)) {
            $data['role'] = 'kepala_bagian';
        }

        return [$data, $tim];
    }

    /** Sinkronkan tabel pivot dan kolom kepala_bagian_id pada tabel bagian. */
    private function terapkanTim(User $user, array $tim): void
    {
        // Tim utama otomatis menjadi anggota
        if ($user->bagian_id && empty($tim[$user->bagian_id])) {
            $tim[$user->bagian_id] = 'anggota';
        }

        // Lepas jabatan ketua di tim yang tidak lagi dipimpin akun ini
        Bagian::where('kepala_bagian_id', $user->id)
            ->whereNotIn('id', array_keys(array_filter($tim, fn($peran) => $peran === 'ketua')))
            ->update(['kepala_bagian_id' => null]);

        DB::table('bagian_user')->where('user_id', $user->id)->delete();

        foreach ($tim as $bagianId => $peran) {
            DB::table('bagian_user')->insert([
                'user_id' => $user->id,
                'bagian_id' => $bagianId,
                'peran_tim' => $peran,
            ]);

            if ($peran === 'ketua') {
                Bagian::where('id', $bagianId)->update(['kepala_bagian_id' => $user->id]);
            }
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        // Cegah admin menghapus akunnya sendiri secara tidak sengaja
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }

    private const PASSWORD_DEFAULT = 'password';
    public function resetPassword(User $user): RedirectResponse
    {
        // Akun sendiri diubah lewat Profil Saya, supaya admin tidak tidak sengaja
        // menurunkan password akunnya sendiri menjadi password default
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Password akun sendiri diubah lewat halaman Profil Saya.');
        }

        $user->forceFill([
            'password' => Hash::make(self::PASSWORD_DEFAULT),
            // Mencabut sesi "ingat saya" yang masih aktif di perangkat lain
            'remember_token' => Str::random(60),
        ])->save();

        // Opsional, hanya jika SESSION_DRIVER=database: keluarkan sesi yang sedang aktif
        // \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', "Password {$user->name} direset ke default: " . self::PASSWORD_DEFAULT);
    }

    public function resendVerification(User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return back()->with('success', "Email {$user->email} sudah terverifikasi.");
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "Email verifikasi dikirim ulang ke {$user->email}.");
    }
}
