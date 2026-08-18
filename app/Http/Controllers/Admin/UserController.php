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
    public function index(): View
    {
        $users = User::with('bagian')->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
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
            // bagian_id wajib untuk staf & kepala_bagian, boleh kosong untuk kepala_bps/admin
            'bagian_id' => ['nullable', 'required_if:role,staf,kepala_bagian', 'exists:bagian,id'],
            'atasan_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['password'] = Hash::make('password'); // password default, wajib diganti saat login pertama
        $data['email_verified_at'] = now();

        $user = User::create($data);

        // Kalau role-nya kepala_bagian, otomatis set sebagai kepala di tabel bagian
        if ($user->role === 'kepala_bagian' && $user->bagian_id) {
            Bagian::where('id', $user->bagian_id)->update(['kepala_bagian_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil dibuat. Password default: password');
    }

    public function edit(User $user): View
    {
        $daftarBagian = Bagian::all();
        $calonAtasan = User::whereIn('role', ['kepala_bagian', 'kepala_bps'])
            ->where('id', '!=', $user->id)->get();

        return view('admin.users.edit', compact('user', 'daftarBagian', 'calonAtasan'));
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
}
