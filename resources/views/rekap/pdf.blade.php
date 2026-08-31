<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #1F3864; padding-bottom: 12px; }
        .header h1 { margin: 0; color: #1F3864; font-size: 18px; }
        .header p { margin: 2px 0 0; color: #6b7280; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1F3864; color: #fff; text-align: left; padding: 8px; font-size: 11px; }
        td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-center { text-align: center; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Laporan Kantor</h1>
        <p>BPS Kota Ambon &middot; Periode {{ $namaBulan }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Bagian</th>
                <th class="text-center">Pegawai</th>
                <th class="text-center">Total Laporan</th>
                <th class="text-center">Disetujui</th>
                <th class="text-center">Menunggu</th>
                <th class="text-center">Dikembalikan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapPerBagian as $item)
                <tr>
                    <td>{{ $item->nama_bagian }} ({{ $item->kode_bagian }})</td>
                    <td class="text-center">{{ $item->pegawai_count }}</td>
                    <td class="text-center">{{ $item->total_laporan }}</td>
                    <td class="text-center">{{ $item->disetujui }}</td>
                    <td class="text-center">{{ $item->menunggu }}</td>
                    <td class="text-center">{{ $item->dikembalikan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
</body>
</html>