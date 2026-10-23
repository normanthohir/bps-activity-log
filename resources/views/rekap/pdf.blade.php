<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kinerja {{ $rekap['periode']['label'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #334155; }
        h1 { font-size: 15px; color: #1F3864; margin: 0 0 2px; }
        h2 { font-size: 11px; color: #1F3864; margin: 18px 0 6px; }
        .muted { color: #64748b; }
        .garis { border-bottom: 2px solid #1F3864; padding-bottom: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1F3864; color: #fff; font-size: 8px; text-align: left; padding: 5px 6px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        tr { page-break-inside: avoid; }
        .c { text-align: center; }
        .ringkas td { border: 1px solid #e2e8f0; padding: 7px 10px; width: 25%; }
        .angka { font-size: 15px; font-weight: bold; color: #0f172a; }
        .badge { padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
        .sangat_baik { background: #dcfce7; color: #15803d; }
        .baik { background: #dbeafe; color: #1d4ed8; }
        .cukup { background: #fef3c7; color: #b45309; }
        .kurang { background: #fee2e2; color: #b91c1c; }
        .kosong { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body>
    @php
        use App\Services\RekapKinerjaService as Rekap;
        $r = $rekap['ringkasan'];
    @endphp

    <div class="garis">
        <h1>Rekap Kinerja Pegawai &ndash; BPS Kota Ambon</h1>
        <div class="muted">
            Periode {{ $rekap['periode']['label'] }} &middot; {{ $rekap['periode']['hari_kerja'] }} hari kerja (Senin&ndash;Jumat)
            &middot; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    <table class="ringkas">
        <tr>
            <td><div class="muted">Rata-rata skor</div><div class="angka">{{ $r['rata_skor'] ?? '-' }}</div></td>
            <td><div class="muted">Pegawai sudah melapor</div><div class="angka">{{ $r['melapor'] }} / {{ $r['jumlah_pegawai'] }}</div></td>
            <td><div class="muted">Total laporan</div><div class="angka">{{ $r['total_laporan'] }}</div></td>
            <td><div class="muted">Tingkat persetujuan</div><div class="angka">{{ $r['tingkat_persetujuan'] !== null ? $r['tingkat_persetujuan'] . '%' : '-' }}</div></td>
        </tr>
    </table>

    <h2>Rekap per bagian</h2>
    <table>
        <thead>
            <tr>
                <th>Bagian</th><th>Kepala Tim</th>
                <th class="c">Pegawai</th><th class="c">Sudah Lapor</th><th class="c">Belum Lapor</th>
                <th class="c">Laporan</th><th class="c">Disetujui</th><th class="c">Dikembalikan</th>
                <th class="c">Skor</th><th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap['bagian'] as $b)
                <tr>
                    <td>{{ $b['nama'] }}</td>
                    <td>{{ $b['kepala'] }}</td>
                    <td class="c">{{ $b['jumlah_pegawai'] }}</td>
                    <td class="c">{{ $b['melapor'] }}</td>
                    <td class="c">{{ $b['belum_lapor'] }}</td>
                    <td class="c">{{ $b['total'] }}</td>
                    <td class="c">{{ $b['disetujui'] }}</td>
                    <td class="c">{{ $b['dikembalikan'] }}</td>
                    <td class="c"><b>{{ $b['skor'] ?? '-' }}</b></td>
                    <td><span class="badge {{ $b['tingkat'] }}">{{ Rekap::LABEL[$b['tingkat']] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Rekap per pegawai</h2>
    <table>
        <thead>
            <tr>
                <th class="c">#</th><th>Nama</th><th>Peran</th><th>Bagian</th>
                <th class="c">Hari Lapor</th><th class="c">Laporan</th><th class="c">Disetujui</th>
                <th class="c">Dikembalikan</th><th class="c">Selesai</th><th class="c">Tertunda</th><th class="c">Gagal</th>
                <th class="c">Skor</th><th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap['pegawai'] as $p)
                <tr>
                    <td class="c">{{ $p['peringkat'] }}</td>
                    <td>{{ $p['nama'] }}</td>
                    <td>{{ $p['peran'] }}</td>
                    <td>{{ $p['bagian'] }}</td>
                    <td class="c">{{ $p['hari_lapor'] }}/{{ $rekap['periode']['hari_kerja'] }}</td>
                    <td class="c">{{ $p['total'] }}</td>
                    <td class="c">{{ $p['disetujui'] }}</td>
                    <td class="c">{{ $p['dikembalikan'] }}</td>
                    <td class="c">{{ $p['selesai'] }}</td>
                    <td class="c">{{ $p['tertunda'] }}</td>
                    <td class="c">{{ $p['gagal'] }}</td>
                    <td class="c"><b>{{ $p['skor'] ?? '-' }}</b></td>
                    <td><span class="badge {{ $p['tingkat'] }}">{{ Rekap::LABEL[$p['tingkat']] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="muted" style="margin-top:14px;">
        Skor = kedisiplinan {{ Rekap::BOBOT['disiplin'] }}% + kualitas {{ Rekap::BOBOT['kualitas'] }}% + hasil kerja {{ Rekap::BOBOT['hasil'] }}%.
        Hari libur nasional dan cuti belum diperhitungkan. Draft tidak dihitung.
    </p>
</body>
</html>