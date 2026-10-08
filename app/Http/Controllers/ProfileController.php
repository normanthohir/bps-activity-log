<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form profil milik user yang login.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update data profil user (nama, NIP, email).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route($this->namaRouteProfile('edit'))
            ->with('status', 'profile-updated');
    }

    /**
     * Hapus akun user yang login.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Helper: tentukan nama route profile yang benar sesuai role user
     * yang login, karena route profile kini tersebar per-role dengan
     * prefix berbeda (profile.edit, kabag.profile.edit, dst).
     */
    private function namaRouteProfile(string $action): string
    {
        $prefix = match (auth()->user()->role) {
            'kepala_bagian' => 'kabag.',
            'kepala_bps' => 'kepala-bps.',
            'admin' => 'admin.',
            default => '', // staf, tanpa prefix
        };

        return $prefix . 'profile.' . $action;
    }
}