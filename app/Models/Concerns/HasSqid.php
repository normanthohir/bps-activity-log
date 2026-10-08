<?php

namespace App\Models\Concerns;

use Sqids\Sqids;

/**
 * Trait ini hanya dipakai di Eloquent Model.
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSqid
{
    protected static function sqids(): Sqids
    {
        return new Sqids(
            alphabet: config('app.sqids_alphabet'),
            minLength: 10,
        );
    }

    // Dipanggil otomatis saat route('laporan.edit', $laporan) dibuat:
    // angka 1 -> "Xk9Pq2mRtB"
    public function getRouteKey(): string
    {
        return static::sqids()->encode([$this->getKey()]);
    }

    // Dipanggil otomatis saat URL dibuka dan Controller menerima
    // (LaporanHarian $laporan): "Xk9Pq2mRtB" -> angka 1 -> cari datanya
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        $decoded = static::sqids()->decode($value);

        // Kode ngawur atau dimanipulasi -> null -> Laravel balas 404
        if (count($decoded) !== 1 || static::sqids()->encode($decoded) !== $value) {
            return null;
        }

        return $this->newQuery()->where($this->getKeyName(), $decoded[0])->first();
    }
}