<?php

namespace App\Support;

/** Warna dan istilah tampilan yang dipakai bersama oleh rekap laporan dan rekap pekerjaan. */
class TampilanRekap
{
    public const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** Warna latar sel predikat di Excel (ARGB). */
    public const WARNA_EXCEL = [
        'sangat_baik' => 'FFDCFCE7',
        'baik' => 'FFDBEAFE',
        'cukup' => 'FFFEF3C7',
        'kurang' => 'FFFEE2E2',
        'kosong' => 'FFF1F5F9',
    ];

    private const GAYA = [
        'sangat_baik' => ['badge' => 'bg-green-50 text-green-700 ring-green-600/20', 'batang' => 'bg-green-500', 'teks' => 'text-green-600'],
        'baik' => ['badge' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'batang' => 'bg-blue-500', 'teks' => 'text-blue-600'],
        'cukup' => ['badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'batang' => 'bg-amber-500', 'teks' => 'text-amber-600'],
        'kurang' => ['badge' => 'bg-red-50 text-red-700 ring-red-600/20', 'batang' => 'bg-red-500', 'teks' => 'text-red-600'],
        'kosong' => ['badge' => 'bg-gray-100 text-gray-600 ring-gray-500/20', 'batang' => 'bg-gray-300', 'teks' => 'text-gray-400'],
    ];

    public static function gaya(string $tingkat): array
    {
        return self::GAYA[$tingkat] ?? self::GAYA['kosong'];
    }
}