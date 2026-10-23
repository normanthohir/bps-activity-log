<?php
namespace App\Support;

use App\Models\Bagian;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AksesPekerjaan
{
    public static function lingkup(User $user): Builder
    {
        $query = Tugas::query();
        if ($user->isKepalaBps() || $user->isAdmin()) return $query;
        if (ModeKerja::ketuaAktif($user)) {
            $ids = ModeKerja::timDipimpinAktifIds($user);
            return $query->where(function ($q) use ($user, $ids) {
                $q->where('ditugaskan_ke', $user->id)
                    ->orWhere('dibuat_oleh', $user->id)
                    ->orWhereIn('bagian_id', $ids);
            });
        }
        return $query->where('ditugaskan_ke', $user->id);
    }

    public static function dapatLihat(User $user, Tugas $tugas): bool
    {
        return self::lingkup($user)->whereKey($tugas->id)->exists();
    }

    public static function timYangBolehDitugaskan(User $user)
    {
        if ($user->isKepalaBps() || $user->isAdmin()) return Bagian::orderBy('kode_bagian')->orderBy('nama_bagian')->get();
        if (ModeKerja::ketuaAktif($user)) return Bagian::whereIn('id', ModeKerja::timDipimpinAktifIds($user))->orderBy('kode_bagian')->orderBy('nama_bagian')->get();
        return Bagian::whereRaw('1 = 0')->get();
    }

    public static function bolehDisposisi(User $user, ?Tugas $induk = null): bool
    {
        if (!ModeKerja::ketuaAktif($user) && !$user->isKepalaBps() && !$user->isAdmin()) return false;
        if (!$induk) return true;
        return self::dapatLihat($user, $induk) &&
            ($user->isKepalaBps() || $user->isAdmin() || $induk->ditugaskan_ke === $user->id);
    }

    public static function tujuanValid(User $pengirim, User $penerima, Bagian $bagian): bool
    {
        // Ketua Tim dapat mengerjakan tugasnya sendiri, namun hanya pada tim yang dipimpinnya.
        // Tidak mengizinkan menyamar sebagai anggota tim lain atau mengubah hak mode Anggota.
        if ($penerima->id === $pengirim->id) {
            return ModeKerja::ketuaAktif($pengirim) && $pengirim->memimpinTim($bagian->id);
        }
        if ($penerima->isAdmin() || $penerima->isKepalaBps()) return false;
        if (!$penerima->tim()->where('bagian.id', $bagian->id)->exists()
            && (int) $bagian->kepala_bagian_id !== (int) $penerima->id) return false;
        if ($pengirim->isKepalaBps() || $pengirim->isAdmin()) return true;
        return ModeKerja::ketuaAktif($pengirim) && $pengirim->memimpinTim($bagian->id)
            && $penerima->isStaf();
    }
}
