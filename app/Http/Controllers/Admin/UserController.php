<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\User;
use App\Support\KeanggotaanTim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['bagian', 'tim'])->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $daftarBagian = Bagian::orderBy('kode_bagian')->orderBy('nama_bagian')->get();
        $calonAtasan = User::where('role', 'kepala_bps')->orWhereHas('timDipimpin')->orderBy('name')->get();
        return view('admin.users.create', compact('daftarBagian', 'calonAtasan'));
    }

    private function validasi(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', Rule::unique('users', 'nip')->ignore($user?->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            // Role login dipertahankan demi kompatibilitas. Jabatan ketua tidak lagi ditentukan oleh nilai ini.
            'role' => ['nullable', 'in:staf,kepala_bps,admin'],
            'bagian_id' => ['nullable', 'exists:bagian,id'],
            'atasan_id' => ['nullable', 'exists:users,id', Rule::notIn(array_filter([$user?->id]))],
            'bagian_ids' => ['nullable', 'array'],
            'bagian_ids.*' => ['integer', 'distinct', 'exists:bagian,id'],
            'ketua_tim_ids' => ['nullable', 'array'],
            'ketua_tim_ids.*' => ['integer', 'distinct', 'exists:bagian,id'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        $data['role'] = $data['role'] ?? 'staf';
        $anggota = $data['bagian_ids'] ?? [];
        $dipimpin = $data['ketua_tim_ids'] ?? [];
        unset($data['bagian_ids'], $data['ketua_tim_ids']);
        DB::transaction(function () use ($data, $anggota, $dipimpin) {
            $user = User::create([...$data, 'password' => Hash::make('password')]);
            $user->forceFill(['email_verified_at' => now()])->save();
            KeanggotaanTim::sinkronPegawai($user, $anggota, $dipimpin);
        });
        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dibuat. Password default: password');
    }

    public function edit(User $user): View
    {
        $daftarBagian = Bagian::orderBy('kode_bagian')->orderBy('nama_bagian')->get();
        $user->load('tim');
        $ketuaTimIds = $user->tim->filter(fn ($tim) => $tim->pivot->peran_tim === 'ketua')->pluck('id')->all();
        $calonAtasan = User::where('id', '!=', $user->id)
            ->where(fn ($q) => $q->where('role', 'kepala_bps')->orWhereHas('timDipimpin'))
            ->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'daftarBagian', 'calonAtasan', 'ketuaTimIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validasi($request, $user);
        $data['role'] = $data['role'] ?? (in_array($user->role, ['admin','kepala_bps'], true) ? $user->role : 'staf');
        $anggota = $data['bagian_ids'] ?? [];
        $dipimpin = $data['ketua_tim_ids'] ?? [];
        unset($data['bagian_ids'], $data['ketua_tim_ids']);
        DB::transaction(function () use ($user, $data, $anggota, $dipimpin) {
            $user->update($data);
            KeanggotaanTim::sinkronPegawai($user, $anggota, $dipimpin);
        });
        return redirect()->route('admin.users.index')->with('success', 'Akun dan keanggotaan/ketua tim berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');
        // Hindari ketua tim tertinggal tanpa penunjukan jelas setelah akun dihapus.
        DB::transaction(function () use ($user) {
            Bagian::where('kepala_bagian_id', $user->id)->update(['kepala_bagian_id' => null]);
            $user->delete();
        });
        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $user->update(['password' => Hash::make('password')]);
        return redirect()->route('admin.users.edit', $user)->with('success', 'Password berhasil direset ke default: password');
    }
}
