<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * API profil untuk aplikasi Flutter.
 * Padanan ProfileController (web) + PasswordController, tetapi membalas JSON
 * dengan format standar: {"success": true, "message": "", "data": {...}}.
 *
 * NIP, bagian, dan atasan hanya dibaca (diatur Admin), sama seperti di web.
 * Hapus akun sengaja TIDAK disediakan di API.
 */
class ProfilApiController extends Controller
{
    /** GET /api/profil */
    public function show(Request $request): JsonResponse
    {
        return $this->ok(['user' => $this->bentuk($request->user())]);
    }

    /** PATCH /api/profil  (nama & email; aturan sama dengan web) */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        $emailBerubah = $user->isDirty('email');

        if ($emailBerubah) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailBerubah) {
            $user->sendEmailVerificationNotification();
        }

        return $this->ok(
            [
                'user' => $this->bentuk($user->fresh()),
                'email_berubah' => $emailBerubah,
            ],
            $emailBerubah
                ? 'Profil tersimpan. Tautan verifikasi dikirim ke email baru Anda.'
                : 'Profil berhasil diperbarui.'
        );
    }

    /** PATCH /api/profil/password */
    public function ubahPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        // Cast 'hashed' pada model User yang meng-hash otomatis.
        $user->update(['password' => $data['password']]);

        // Keluarkan perangkat lain; perangkat ini tetap login.
        $tokenSaatIni = $request->user()->currentAccessToken();
        $user->tokens()
            ->when($tokenSaatIni?->id ?? null, fn ($q, $id) => $q->where('id', '!=', $id))
            ->delete();

        return $this->ok(null, 'Password berhasil diubah.');
    }

    /** POST /api/profil/verifikasi-email */
    public function kirimUlangVerifikasi(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->ok(null, 'Email Anda sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        return $this->ok(null, 'Tautan verifikasi baru telah dikirim ke email Anda.');
    }

    // ── Helper ──────────────────────────────────────────

    private function ok(mixed $data = null, string $message = ''): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
        ]);
    }

    /** Bentuk JSON profil; isi sama dengan kartu identitas di profile.edit. */
    private function bentuk(User $u): array
    {
        $u->loadMissing(['bagian', 'atasan']);

        return [
            'id' => $u->id,
            'name' => $u->name,
            'nip' => $u->nip,
            'email' => $u->email,
            'role' => $u->role,
            'nama_role' => match ($u->role) {
                'kepala_bagian' => 'Kepala Tim',
                'kepala_bps' => 'Kepala BPS Kota',
                'admin' => 'Admin',
                default => 'Staf',
            },
            'bagian' => $u->bagian?->nama_bagian
                ?? ($u->role === 'kepala_bps' ? 'Seluruh bagian' : null),
            'atasan' => $u->atasan?->name,
            'email_terverifikasi' => $u->hasVerifiedEmail(),
            'jumlah_anggota' => $u->role === 'kepala_bagian'
                ? $u->bawahan()->count()
                : null,
            'terdaftar_sejak' => $u->created_at?->toIso8601String(),
        ];
    }
}