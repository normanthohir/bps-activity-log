<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ->paginate(25)
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', 'unique:users,nip'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:staf,kepala_bagian,kepala_bps,admin'],
            'bagian_id' => ['nullable', 'required_if:role,staf,kepala_bagian', 'exists:bagian,id'],
            'atasan_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['password'] = Hash::make('password'); // password default
        // email_verified_at sengaja TIDAK diisi, jadi pegawai wajib verifikasi email dulu

        $user = User::create($data);

        if ($user->role === 'kepala_bagian' && $user->bagian_id) {
            Bagian::where('id', $user->bagian_id)->update(['kepala_bagian_id' => $user->id]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            // Akun tetap tersimpan walau email gagal terkirim (misal SMTP bermasalah)
            report($e);

            return redirect()->route('admin.users.index')
                ->with('warning', "Akun {$user->name} dibuat, tetapi email verifikasi gagal dikirim. Gunakan tombol \"Kirim ulang verifikasi\" di daftar user.");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Akun dibuat. Email verifikasi dikirim ke {$user->email}. Password awal: password");
    }

    // public function edit(User $user): View
    // {
    //     $daftarBagian = Bagian::all();
    //     $calonAtasan = User::whereIn('role', ['kepala_bagian', 'kepala_bps'])
    //         ->where('id', '!=', $user->id)->get();

    //     return view('admin.users.edit', compact('user', 'daftarBagian', 'calonAtasan'));
    // }
    public function edit(User $user): View
    {
        $daftarBagian = Bagian::all();
        $calonAtasan = User::whereIn('role', ['kepala_bagian', 'kepala_bps'])
            ->where('id', '!=', $user->id)->get();

        // AMBIL DATA KETUA TIM DARI TABEL PIVOT BAGIAN_USER (Solusi Error)
        $ketuaTimIds = \DB::table('bagian_user')
            ->where('user_id', $user->id)
            ->where('peran_tim', 'ketua') // Sesuaikan string 'ketua' jika nama perannya berbeda di db Anda
            ->pluck('bagian_id')
            ->toArray();

        // Kirimkan variabel $ketuaTimIds ke View
        return view('admin.users.edit', compact('user', 'daftarBagian', 'calonAtasan', 'ketuaTimIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', 'unique:users,nip,' . $user->id],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:staf,kepala_bagian,kepala_bps,admin'],
            'bagian_id' => ['nullable', 'required_if:role,staf,kepala_bagian', 'exists:bagian,id'],
            'atasan_id' => ['nullable', 'exists:users,id'],
        ]);

        $user->update($data);

        if ($user->role === 'kepala_bagian' && $user->bagian_id) {
            Bagian::where('id', $user->bagian_id)->update(['kepala_bagian_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Cegah admin menghapus akunnya sendiri secara tidak sengaja
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $user->update(['password' => Hash::make('password')]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Password berhasil direset ke default: password');
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
