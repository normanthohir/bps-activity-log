<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Menyiapkan kalender bulanan dengan batang pekerjaan yang membentang lintas tanggal/minggu. */
class KalenderPekerjaan
{
    public static function buat(Request $request): array
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        if (!is_string($bulan) || !preg_match('/^20\d{2}-(0[1-9]|1[0-2])$/D', $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $awal = Carbon::createFromFormat('!Y-m-d', $bulan.'-01');
        $akhir = $awal->copy()->endOfMonth();
        $mulai = $awal->copy()->startOfWeek(Carbon::MONDAY);
        $selesai = $akhir->copy()->endOfWeek(Carbon::SUNDAY);
        $mulaiString = $mulai->toDateString();
        $selesaiString = $selesai->toDateString();

        // Query memakai irisan rentang, bukan hanya mencari tenggat di bulan ini.
        // Pekerjaan yang dimulai bulan lalu dan belum selesai tetap ditampilkan.
        $pekerjaan = AksesPekerjaan::lingkup($request->user())
            ->with('penerimaTugas')
            ->where(function ($query) use ($mulaiString, $selesaiString, $mulai, $selesai) {
                $query->where(function ($q) use ($mulaiString, $selesaiString) {
                    $q->whereNotNull('tanggal_mulai')->whereNotNull('tenggat')
                        ->whereDate('tanggal_mulai', '<=', $selesaiString)
                        ->whereDate('tenggat', '>=', $mulaiString);
                })->orWhere(function ($q) use ($mulaiString, $selesaiString) {
                    $q->whereNotNull('tanggal_mulai')->whereNull('tenggat')
                        ->whereBetween('tanggal_mulai', [$mulaiString, $selesaiString]);
                })->orWhere(function ($q) use ($mulaiString, $selesaiString) {
                    $q->whereNull('tanggal_mulai')->whereNotNull('tenggat')
                        ->whereBetween('tenggat', [$mulaiString, $selesaiString]);
                })->orWhere(function ($q) use ($mulai, $selesai) {
                    $q->whereNull('tanggal_mulai')->whereNull('tenggat')
                        ->whereBetween('created_at', [
                            $mulai->copy()->startOfDay(),
                            $selesai->copy()->endOfDay(),
                        ]);
                });
            })
            ->orderBy('tanggal_mulai')
            ->orderBy('tenggat')
            ->orderBy('id')
            ->get();

        $rentang = $pekerjaan->map(function ($tugas) {
            $tanggalMulai = $tugas->tanggal_mulai ?? $tugas->tenggat ?? $tugas->created_at;
            $tanggalSelesai = $tugas->tenggat ?? $tugas->tanggal_mulai ?? $tugas->created_at;
            if (!$tanggalMulai || !$tanggalSelesai) {
                return null;
            }

            $awal = $tanggalMulai->copy()->startOfDay();
            $akhir = $tanggalSelesai->copy()->startOfDay();
            // Data lama dengan tenggat sebelum tanggal mulai ditampilkan pada
            // tanggal mulai saja, bukan menggambar rentang mundur yang keliru.
            if ($akhir->lt($awal)) {
                $akhir = $awal->copy();
            }

            return ['tugas' => $tugas, 'awal' => $awal, 'akhir' => $akhir];
        })->filter()->values();

        $jumlah = $rentang->filter(fn ($item) =>
            $item['awal']->lte($akhir) && $item['akhir']->gte($awal)
        )->count();

        $minggu = collect();
        $daftarTersembunyi = []; // YYYY-MM-DD => pekerjaan yang tidak masuk lima baris
        $barisSebelumnya = [];
        $batasBaris = 5;
        for ($senin = $mulai->copy(); $senin->lte($selesai); $senin->addWeek()) {
            $mingguTerakhir = $senin->copy()->addDays(6);
            $yangTampil = $rentang->filter(fn ($item) =>
                $item['awal']->lte($mingguTerakhir) && $item['akhir']->gte($senin)
            )->sort(function ($a, $b) use ($barisSebelumnya) {
                // SOP induk lebih dulu; langkah SOP berikutnya; pekerjaan biasa terakhir.
                // Prioritas berlaku pada keseluruhan minggu sehingga batang lintas-hari
                // tidak terpotong di tengah oleh pekerjaan berprioritas lebih rendah.
                $prioritas = static function ($item): int {
                    $tugas = $item['tugas'];
                    if ($tugas->sop_pelaksanaan_id && !$tugas->sop_langkah_id) return 0;
                    if ($tugas->sop_langkah_id || $tugas->sop_pelaksanaan_id) return 1;
                    return 2;
                };
                $beda = $prioritas($a) <=> $prioritas($b);
                if ($beda !== 0) return $beda;
                $barisA = $barisSebelumnya[$a['tugas']->id] ?? 99;
                $barisB = $barisSebelumnya[$b['tugas']->id] ?? 99;
                if ($barisA !== $barisB) return $barisA <=> $barisB;
                $beda = $a['awal']->timestamp <=> $b['awal']->timestamp;
                if ($beda !== 0) return $beda;
                $durasiA = $a['awal']->diffInDays($a['akhir']);
                $durasiB = $b['awal']->diffInDays($b['akhir']);
                if ($durasiA !== $durasiB) return $durasiB <=> $durasiA;
                return $a['tugas']->id <=> $b['tugas']->id;
            })->values();

            $terisi = []; // [baris 0..4][indeks hari 0..6] = true
            $barisBerikutnya = [];
            $segmen = collect();
            $tersembunyiPekanIni = [];

            foreach ($yangTampil as $item) {
                $tugas = $item['tugas'];
                $segmenAwal = $item['awal']->greaterThan($senin) ? $item['awal']->copy() : $senin->copy();
                $segmenAkhir = $item['akhir']->lessThan($mingguTerakhir) ? $item['akhir']->copy() : $mingguTerakhir->copy();
                $hariPertama = (int) $senin->diffInDays($segmenAwal);
                $panjang = (int) $segmenAwal->diffInDays($segmenAkhir) + 1;

                // Sebisa mungkin letakkan kelanjutan batang pada lajur yang sama.
                $pilihan = [];
                if (isset($barisSebelumnya[$tugas->id])) {
                    $pilihan[] = $barisSebelumnya[$tugas->id];
                }
                foreach (range(0, $batasBaris - 1) as $kandidat) {
                    if (!in_array($kandidat, $pilihan, true)) $pilihan[] = $kandidat;
                }
                $baris = null;
                foreach ($pilihan as $kandidat) {
                    if (self::barisKosong($terisi, $kandidat, $hariPertama, $panjang)) {
                        $baris = $kandidat;
                        break;
                    }
                }
                if ($baris === null) {
                    // Seluruh rentang segmen disembunyikan; tugas tetap bisa dibuka
                    // melalui tombol +N lainnya pada masing-masing tanggal.
                    for ($i = 0; $i < $panjang; $i++) {
                        $key = $segmenAwal->copy()->addDays($i)->toDateString();
                        $tersembunyiPekanIni[$key][$tugas->id] = $item;
                    }
                    continue;
                }

                for ($i = $hariPertama; $i < $hariPertama + $panjang; $i++) {
                    $terisi[$baris][$i] = true;
                }
                $barisBerikutnya[$tugas->id] = $baris;
                $segmen->push([
                    'tugas' => $tugas,
                    'kolom_mulai' => $hariPertama + 1,
                    'panjang' => $panjang,
                    'baris' => $baris + 2,
                    'lanjut_dari_minggu_lalu' => $item['awal']->lt($senin),
                    'lanjut_minggu_depan' => $item['akhir']->gt($mingguTerakhir),
                    'awal_asli' => $item['awal'],
                    'akhir_asli' => $item['akhir'],
                ]);
            }

            foreach ($tersembunyiPekanIni as $tanggal => $tugasTersembunyi) {
                $daftarTersembunyi[$tanggal] = array_values(array_map(static function ($item) {
                    $tugas = $item['tugas'];
                    return [
                        'judul' => $tugas->judul,
                        'url' => route('pekerjaan.show', $tugas),
                        'status' => $tugas->status,
                        'pelaksana' => $tugas->penerimaTugas?->name ?? 'Belum ada pelaksana',
                        'awal' => $item['awal']->format('d/m/Y'),
                        'akhir' => $item['akhir']->format('d/m/Y'),
                        'sop' => (bool) ($tugas->sop_pelaksanaan_id || $tugas->sop_langkah_id),
                    ];
                }, $tugasTersembunyi));
            }

            $minggu->push([
                'hari' => collect(range(0, 6))->map(function ($n) use ($senin, $tersembunyiPekanIni) {
                    $tanggal = $senin->copy()->addDays($n);
                    return [
                        'tanggal' => $tanggal,
                        'key' => $tanggal->toDateString(),
                        'lainnya' => count($tersembunyiPekanIni[$tanggal->toDateString()] ?? []),
                    ];
                }),
                'segmen' => $segmen,
                'jumlah_baris' => max(2, empty($terisi) ? 0 : max(array_keys($terisi)) + 1),
            ]);
            // Pertahankan urutan relatif kelanjutan pada minggu berikutnya.
            $barisSebelumnya = $barisBerikutnya;
        }

        return [
            'label' => $awal->translatedFormat('F Y'),
            'bulan' => $awal->format('Y-m'),
            'sebelumnya' => $awal->copy()->subMonth()->format('Y-m'),
            'berikutnya' => $awal->copy()->addMonth()->format('Y-m'),
            'minggu' => $minggu,
            'jumlah' => $jumlah,
            'daftar_tersembunyi' => $daftarTersembunyi,
            'batas_baris' => $batasBaris,
        ];
    }

    private static function barisKosong(array $terisi, int $baris, int $hariPertama, int $panjang): bool
    {
        for ($i = $hariPertama; $i < $hariPertama + $panjang; $i++) {
            if (!empty($terisi[$baris][$i])) {
                return false;
            }
        }
        return true;
    }
}
