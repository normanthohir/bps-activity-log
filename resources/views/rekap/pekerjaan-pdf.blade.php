<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Pekerjaan {{ $rekap['periode']['label'] }}</title>
    @include('rekap.partials.pdf-gaya')
</head>
<body>
    @php
        use App\Services\RekapPekerjaanService as Rekap;
        $r = $rekap['ringkasan'];
    @endphp

    <div class="garis">
        <h1>Rekap Pekerjaan Pegawai &ndash; BPS Kota Ambon</h1>
        <div class="muted">
            Periode {{ $rekap['periode']['label'] }} (berdasarkan tenggat) &middot; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    <table class="ringkas">
        <tr>
            <td><div class="muted">Rata-rata skor</div><div class="angka">{{ $r['rata_skor'] ?? '-' }}</div></td>
            <td><div class="muted">Pegawai punya tugas</div><div class="angka">{{ $r['punya_tugas'] }} / {{ $r['jumlah_pegawai'] }}</div></td>
            <td><div class="muted">Tugas selesai</div><div class="angka">{{ $r['selesai'] }} / {{ $r['total_tugas'] }}</div></td>
            <td><div class="muted">Ketepatan waktu</div><div class="angka">{{ $r['tepat_waktu'] !== null ? $r['tepat_waktu'] . '%' : '-' }}</div></td>
        </tr>
    </table>

    <h2>Rekap per bagian</h2>
    <table>
        <thead>
            <tr>
                <th>Bagian</th><th>Kepala Tim</th>
                <th class="c">Anggota</th><th class="c">Tugas</th><th class="c">Selesai</th><th class="c">Terlambat</th>
                <th class="c">Skor</th><th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap['bagian'] as $b)
                <tr>
                    <td>{{ $b['nama'] }}</td>
                    <td>{{ $b['kepala'] }}</td>
                    <td class="c">{{ $b['anggota'] }}</td>
                    <td class="c">{{ $b['total'] }}</td>
                    <td class="c">{{ $b['selesai'] }}</td>
                    <td class="c">{{ $b['terlambat'] }}</td>
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
                <th class="c">Tugas</th><th class="c">Selesai</th><th class="c">Dikerjakan</th><th class="c">Belum</th>
                <th class="c">Terlambat</th><th class="c">Tepat Waktu</th><th class="c">Dilaporkan</th>
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
                    <td class="c">{{ $p['total'] }}</td>
                    <td class="c">{{ $p['selesai'] }}</td>
                    <td class="c">{{ $p['dikerjakan'] }}</td>
                    <td class="c">{{ $p['belum'] }}</td>
                    <td class="c">{{ $p['terlambat'] }}</td>
                    <td class="c">{{ $p['tepat'] }}</td>
                    <td class="c">{{ $p['terlapor'] }}</td>
                    <td class="c"><b>{{ $p['skor'] ?? '-' }}</b></td>
                    <td><span class="badge {{ $p['tingkat'] }}">{{ Rekap::LABEL[$p['tingkat']] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($rekap['terlambat']->isNotEmpty())
        <h2>Pekerjaan sedang terlambat (5 teratas)</h2>
        <table>
            <thead><tr><th>Pekerjaan</th><th>Pelaksana</th><th>Bagian</th><th>Tenggat</th><th class="c">Terlambat</th></tr></thead>
            <tbody>
                @foreach ($rekap['terlambat'] as $t)
                    <tr>
                        <td>{{ $t->judul }}</td>
                        <td>{{ $t->penerimaTugas?->name ?? '-' }}</td>
                        <td>{{ $t->bagian?->nama_bagian ?? '-' }}</td>
                        <td>{{ $t->tenggat->format('d M Y') }}</td>
                        <td class="c">{{ abs((int) $t->tenggat->copy()->startOfDay()->diffInDays(today())) }} hari</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="muted" style="margin-top:14px;">
        Skor = penyelesaian {{ Rekap::BOBOT['penyelesaian'] }}% + ketepatan waktu {{ Rekap::BOBOT['ketepatan'] }}% + pelaporan {{ Rekap::BOBOT['pelaporan'] }}%.
        Periode menurut tenggat tugas. Waktu selesai diambil dari perubahan terakhir pada tugas.
    </p>
</body>
</html>