<?php
namespace App\Support;

use App\Models\Bagian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Role ketua/anggota terikat pada pasangan (pegawai, tim), bukan role login global. */
class KeanggotaanTim
{
    public static function tetapkanKetua(Bagian $tim, ?User $ketua): void
    {
        if ($ketua && ($ketua->isAdmin() || $ketua->isKepalaBps())) {
            throw ValidationException::withMessages(['kepala_bagian_id' => 'Ketua tim harus akun pegawai, bukan admin/Kepala BPS.']);
        }
        $sebelumnya = $tim->kepala_bagian_id;
        $tim->update(['kepala_bagian_id' => $ketua?->id]);
        if ($sebelumnya && $sebelumnya !== $ketua?->id) {
            DB::table('bagian_user')->where('bagian_id', $tim->id)->where('user_id', $sebelumnya)
                ->update(['peran_tim' => 'anggota']);
        }
        if ($ketua) {
            $tim->anggotaTim()->syncWithoutDetaching([$ketua->id => ['peran_tim' => 'ketua']]);
        }
    }

    /** Saat menyunting akun, tim yang dipimpin dipilih secara eksplisit dan dapat lebih dari satu. */
    public static function sinkronPegawai(User $pegawai, array $anggotaIds, array $ketuaIds): void
    {
        $anggotaIds = array_values(array_unique(array_map('intval', array_filter($anggotaIds))));
        $ketuaIds = array_values(array_unique(array_map('intval', array_filter($ketuaIds))));
        if (($pegawai->isAdmin() || $pegawai->isKepalaBps()) && $ketuaIds) {
            throw ValidationException::withMessages(['ketua_tim_ids' => 'Admin dan Kepala BPS tidak dapat menjadi ketua tim operasional.']);
        }
        $semuaIds = array_values(array_unique(array_merge($anggotaIds, $ketuaIds, array_filter([$pegawai->bagian_id]))));
        DB::transaction(function () use ($pegawai, $ketuaIds, $semuaIds) {
            // Lock tim terpilih agar dua akun tidak sama-sama menggantikan ketua secara diam-diam.
            $dipimpin = Bagian::whereIn('id', $ketuaIds)->lockForUpdate()->get();
            foreach ($dipimpin as $tim) {
                if ($tim->kepala_bagian_id && $tim->kepala_bagian_id !== $pegawai->id) {
                    throw ValidationException::withMessages([
                        'ketua_tim_ids' => "Tim {$tim->nama_bagian} sudah dipimpin pegawai lain. Ubah dari menu Edit Tim dahulu.",
                    ]);
                }
            }
            foreach (Bagian::where('kepala_bagian_id', $pegawai->id)->get() as $lama) {
                if (!in_array($lama->id, $ketuaIds, true)) {
                    $lama->update(['kepala_bagian_id' => null]);
                }
            }
            foreach ($dipimpin as $tim) {
                $tim->update(['kepala_bagian_id' => $pegawai->id]);
            }
            $pegawai->tim()->sync(collect($semuaIds)->mapWithKeys(fn ($id) => [
                $id => ['peran_tim' => in_array($id, $ketuaIds, true) ? 'ketua' : 'anggota'],
            ])->all());
        });
    }
}
