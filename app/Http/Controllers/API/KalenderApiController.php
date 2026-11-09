<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\KalenderPekerjaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KalenderApiController extends Controller
{
    /**
     * GET /api/kalender?bulan=YYYY-MM
     *
     * Memakai KalenderPekerjaan::buat() yang sama dengan website, jadi hak akses
     * (AksesPekerjaan::lingkup) dan penataan batang per minggu otomatis sama.
     * Bentuk JSON dirapikan untuk Flutter:
     *  - tanggal berupa string YYYY-MM-DD (bukan objek Carbon)
     *  - hari_mulai dan baris berbasis 0 (di web: kolom_mulai - 1 dan baris - 2)
     *  - tiap pekerjaan membawa sqid untuk membuka /api/pekerjaan/{sqid}
     *  - daftar_tersembunyi selalu objek JSON (bukan array kosong)
     */
    public function index(Request $request): JsonResponse
    {
        $kalender = KalenderPekerjaan::buat($request);

        $minggu = collect($kalender['minggu'])->map(function (array $pekan) use ($kalender) {
            return [
                'jumlah_baris' => $pekan['jumlah_baris'],
                'hari' => collect($pekan['hari'])->map(fn (array $hari) => [
                    'tanggal' => $hari['key'],
                    'hari_ini' => $hari['tanggal']->isToday(),
                    'bulan_ini' => $hari['tanggal']->format('Y-m') === $kalender['bulan'],
                    'lainnya' => $hari['lainnya'],
                ])->values(),
                'segmen' => collect($pekan['segmen'])->map(function (array $segmen) {
                    $tugas = $segmen['tugas'];

                    return [
                        'id' => $tugas->id,
                        'sqid' => (string) $tugas->getRouteKey(),
                        'judul' => $tugas->judul,
                        'status' => $tugas->status,
                        'sop' => (bool) ($tugas->sop_pelaksanaan_id || $tugas->sop_langkah_id),
                        'pelaksana' => $tugas->penerimaTugas?->name ?? 'Belum ada pelaksana',
                        'hari_mulai' => $segmen['kolom_mulai'] - 1,
                        'panjang' => $segmen['panjang'],
                        'baris' => $segmen['baris'] - 2,
                        'lanjut_dari_minggu_lalu' => $segmen['lanjut_dari_minggu_lalu'],
                        'lanjut_minggu_depan' => $segmen['lanjut_minggu_depan'],
                        'awal' => $segmen['awal_asli']->toDateString(),
                        'akhir' => $segmen['akhir_asli']->toDateString(),
                    ];
                })->values(),
            ];
        })->values();

        $tersembunyi = collect($kalender['daftar_tersembunyi'])->map(
            fn (array $daftar) => collect($daftar)->map(fn (array $item) => [
                // url web berakhir dengan sqid: /pekerjaan/{sqid}
                'sqid' => basename((string) parse_url($item['url'], PHP_URL_PATH)),
                'judul' => $item['judul'],
                'status' => $item['status'],
                'pelaksana' => $item['pelaksana'],
                'sop' => $item['sop'],
                'awal' => Carbon::createFromFormat('d/m/Y', $item['awal'])->toDateString(),
                'akhir' => Carbon::createFromFormat('d/m/Y', $item['akhir'])->toDateString(),
            ])->values()
        );

        return response()->json([
            'success' => true,
            'message' => 'Kalender pekerjaan berhasil diambil.',
            'data' => [
                'bulan' => $kalender['bulan'],
                'sebelumnya' => $kalender['sebelumnya'],
                'berikutnya' => $kalender['berikutnya'],
                'jumlah' => $kalender['jumlah'],
                'batas_baris' => $kalender['batas_baris'],
                'minggu' => $minggu,
                // (object) agar kosong tetap {} dan bukan [] di JSON
                'daftar_tersembunyi' => (object) $tersembunyi->all(),
            ],
        ]);
    }
}