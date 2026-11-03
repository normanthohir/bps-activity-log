{{-- resources/views/rekap/pekerjaan.blade.php (rekap pekerjaan / penugasan) --}}
@php
    use App\Services\RekapPekerjaanService as Rekap;
    use App\Support\TampilanRekap as T;

    $r = $rekap['ringkasan'];
    $label = Rekap::LABEL;
    $parameterUnduh = ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $bagianId];
    $bagianDipilih = $daftarBagian->firstWhere('id', $bagianId);

    $tingkatRata = \App\Services\RekapKinerjaService::tingkat($r['rata_skor'], $r['total_tugas']);
    $gayaRata = T::gaya($tingkatRata);

    $kartu = [
        ['label' => 'Rata-rata skor', 'nilai' => $r['rata_skor'] ?? '-', 'nilai_gaya' => $gayaRata['teks'],
            'badge' => ['teks' => $label[$tingkatRata], 'gaya' => $gayaRata['badge']]],
        ['label' => 'Pegawai punya tugas', 'nilai' => $r['punya_tugas'], 'pembilang' => $r['jumlah_pegawai'],
            'sub' => $r['tanpa_tugas'] ? $r['tanpa_tugas'] . ' orang tidak punya tugas' : 'Semua punya tugas',
            'sub_gaya' => $r['tanpa_tugas'] ? 'text-amber-600' : 'text-gray-400'],
        ['label' => 'Tugas selesai', 'nilai' => $r['selesai'], 'pembilang' => $r['total_tugas'],
            'sub' => $r['dikerjakan'] . ' dikerjakan · ' . $r['belum'] . ' belum dikerjakan'],
        ['label' => 'Ketepatan waktu', 'nilai' => $r['tepat_waktu'] !== null ? $r['tepat_waktu'] . '%' : '-',
            'sub' => $r['terlambat'] ? $r['terlambat'] . ' tugas sedang terlambat' : 'Tidak ada yang terlambat',
            'sub_gaya' => $r['terlambat'] ? 'text-red-600' : 'text-gray-400'],
    ];
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
        x-data="{ tab: @js(request('tab') === 'bagian' ? 'bagian' : 'pegawai'), cari: '' }">

        @include('rekap.partials.kepala', [
            'aktif' => 'pekerjaan',
            'judul' => 'Rekap Pekerjaan Kantor',
            'periode' => $rekap['periode']['label'],
            'keterangan' => 'berdasarkan tenggat',
            'routeExcel' => 'kepala-bps.rekap.pekerjaan.export-excel',
            'routePdf' => 'kepala-bps.rekap.pekerjaan.export-pdf',
        ])

        @include('rekap.partials.filter', ['action' => route('kepala-bps.rekap.pekerjaan')])

        @if ($rekap['pegawai']->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16 px-4">
                <p class="text-sm font-medium text-gray-900">Tidak ada pegawai pada filter ini</p>
                <p class="text-sm text-gray-500 mt-1">Pilih bagian lain atau reset filter.</p>
            </div>
        @else

            @include('rekap.partials.kartu', ['kartu' => $kartu])

            @include('rekap.partials.peringkat', [
                'terbaik' => $r['terbaik'],
                'pembinaan' => $r['pembinaan'],
                'ketPembinaan' => fn ($p) => $p['bagian'] . ' · ' . ($p['terlambat'] ? $p['terlambat'] . ' tugas terlambat' : $p['selesai'] . '/' . $p['total'] . ' selesai'),
            ])

            {{-- Khusus rekap pekerjaan: daftar yang sedang terlambat --}}
            @if ($rekap['terlambat']->isNotEmpty())
                <div class="bg-white rounded-xl border border-red-200 overflow-hidden mb-6">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-red-100 bg-red-50/60">
                        <h2 class="font-semibold text-red-900 text-sm">Pekerjaan sedang terlambat</h2>
                        <span class="text-xs text-red-700">{{ $r['terlambat'] }} tugas, 5 teratas</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($rekap['terlambat'] as $t)
                            @php $hari = abs((int) $t->tenggat->copy()->startOfDay()->diffInDays(today())); @endphp
                            <li>
                                <a href="{{ route('pekerjaan.show', $t) }}"
                                    class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50/70 transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $t->judul }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $t->penerimaTugas?->name ?? '-' }} &middot; {{ $t->bagian?->nama_bagian ?? '-' }}
                                            &middot; tenggat {{ $t->tenggat->format('d M Y') }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold text-red-700 bg-red-50 ring-1 ring-inset ring-red-600/20 px-2.5 py-1 rounded-full">
                                        {{ $hari }} hari
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('rekap.partials.pengalih-tab')

            {{-- Per pegawai --}}
            <div x-show="tab === 'pegawai'" class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-medium w-12">#</th>
                                <th class="py-3 px-4 font-medium">Pegawai</th>
                                <th class="py-3 px-4 font-medium hidden md:table-cell">Progres tugas</th>
                                <th class="py-3 px-4 font-medium text-center hidden sm:table-cell">Tugas</th>
                                <th class="py-3 px-4 font-medium text-center hidden lg:table-cell">Terlambat</th>
                                <th class="py-3 px-4 font-medium text-center hidden xl:table-cell">Tepat waktu</th>
                                <th class="py-3 px-4 font-medium">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rekap['pegawai'] as $p)
                                @php $tot = max(1, $p['total']); @endphp
                                <tr x-show="cari === '' || $el.dataset.cari.includes(cari.toLowerCase())"
                                    data-cari="{{ strtolower($p['nama'] . ' ' . $p['bagian']) }}"
                                    class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-4 text-gray-400 font-medium">
                                        {{ $p['skor'] !== null ? $p['peringkat'] : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @include('rekap.partials.sel-pegawai', ['nama' => $p['nama'], 'sub' => $p['peran'] . ' · ' . $p['bagian']])
                                    </td>
                                    <td class="py-3 px-4 hidden md:table-cell">
                                        <div class="min-w-[9rem]">
                                            <div class="flex h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                                <div class="bg-green-500" style="width: {{ round($p['selesai'] / $tot * 100) }}%"></div>
                                                <div class="bg-blue-500" style="width: {{ round($p['dikerjakan'] / $tot * 100) }}%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $p['total'] === 0 ? 'Tidak ada tugas' : $p['selesai'] . '/' . $p['total'] . ' selesai' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700 hidden sm:table-cell">{{ $p['total'] }}</td>
                                    <td class="py-3 px-4 text-center hidden lg:table-cell {{ $p['terlambat'] ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $p['terlambat'] }}</td>
                                    <td class="py-3 px-4 text-center text-gray-600 hidden xl:table-cell">
                                        {{ $p['ketepatan'] !== null ? round($p['ketepatan']) . '%' : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @include('rekap.partials.sel-skor', ['skor' => $p['skor'], 'tingkat' => $p['tingkat'], 'label' => $label])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per bagian --}}
            <div x-show="tab === 'bagian'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach ($rekap['bagian'] as $i => $b)
                    @include('rekap.partials.kartu-bagian', [
                        'no' => $i + 1,
                        'b' => $b,
                        'label' => $label,
                        'stats' => [
                            ['Anggota', $b['anggota'], 'text-gray-900'],
                            ['Tugas', $b['total'], 'text-gray-900'],
                            ['Selesai', $b['selesai'], 'text-green-600'],
                            ['Terlambat', $b['terlambat'], $b['terlambat'] ? 'text-red-600' : 'text-gray-400'],
                        ],
                        'tautan' => route('kepala-bps.rekap.pekerjaan', ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $b['id'], 'tab' => 'pegawai']),
                    ])
                @endforeach
            </div>

            @include('rekap.partials.cara-hitung', [
                'pembuka' => 'Skor 0–100 dari tiga komponen, dihitung dari tugas yang tenggatnya jatuh pada periode ini:',
                'komponen' => [
                    ['Penyelesaian', Rekap::BOBOT['penyelesaian'], 'tugas selesai dibagi tugas yang sudah selesai atau sudah lewat tenggat. Tugas yang tenggatnya belum tiba tidak mengurangi skor.'],
                    ['Ketepatan waktu', Rekap::BOBOT['ketepatan'], 'tugas selesai pada atau sebelum tenggat dibagi seluruh tugas selesai.'],
                    ['Pelaporan', Rekap::BOBOT['pelaporan'], 'tugas selesai yang sudah punya laporan harian dibagi seluruh tugas selesai.'],
                ],
                'catatan' => [
                    'Tugas masuk ke periode menurut tenggatnya (atau tanggal mulai, atau tanggal dibuat, kalau tenggat kosong). Waktu selesai diambil dari perubahan terakhir pada tugas, karena aplikasi belum menyimpan tanggal selesai tersendiri.',
                    'Setiap pelaksana dihitung dari tugasnya sendiri. Tugas SOP dan disposisi turunan dihitung seperti tugas biasa. Skor bagian dihitung dari seluruh tugas milik bagian itu, bukan rata-rata skor anggotanya.',
                    'Komponen yang belum ada datanya dilewati dan bobotnya dibagi ulang. Pegawai tanpa tugas jatuh tempo tidak diberi skor.',
                ],
            ])
        @endif
    </div>
</x-app-layout>