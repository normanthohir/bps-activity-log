{{-- resources/views/rekap/index.blade.php (rekap laporan harian) --}}
@php
    use App\Services\RekapKinerjaService as Rekap;

    $r = $rekap['ringkasan'];
    $label = Rekap::LABEL;
    $parameterUnduh = ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $bagianId];
    $bagianDipilih = $daftarBagian->firstWhere('id', $bagianId);

    $tingkatRata = Rekap::tingkat($r['rata_skor'], max($r['total_laporan'], $r['rata_skor'] !== null ? 1 : 0));
    $gayaRata = \App\Support\TampilanRekap::gaya($tingkatRata);

    $kartu = [
        ['label' => 'Rata-rata skor', 'nilai' => $r['rata_skor'] ?? '-', 'nilai_gaya' => $gayaRata['teks'],
            'badge' => ['teks' => $label[$tingkatRata], 'gaya' => $gayaRata['badge']]],
        ['label' => 'Pegawai sudah melapor', 'nilai' => $r['melapor'], 'pembilang' => $r['jumlah_pegawai'],
            'sub' => $r['belum_lapor'] ? $r['belum_lapor'] . ' orang belum melapor' : 'Semua sudah melapor',
            'sub_gaya' => $r['belum_lapor'] ? 'text-amber-600' : 'text-gray-400'],
        ['label' => 'Total laporan', 'nilai' => $r['total_laporan'], 'sub' => 'Tidak termasuk draft'],
        ['label' => 'Tingkat persetujuan', 'nilai' => $r['tingkat_persetujuan'] !== null ? $r['tingkat_persetujuan'] . '%' : '-',
            'sub' => 'Disetujui dari yang sudah diproses'],
    ];
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
        x-data="{ tab: @js(request('tab') === 'bagian' ? 'bagian' : 'pegawai'), cari: '' }">

        @include('rekap.partials.kepala', [
            'aktif' => 'laporan',
            'judul' => 'Rekap Laporan Kantor',
            'periode' => $rekap['periode']['label'],
            'keterangan' => $rekap['periode']['hari_kerja'] . ' hari kerja',
            'routeExcel' => 'kepala-bps.rekap.export-excel',
            'routePdf' => 'kepala-bps.rekap.export-pdf',
        ])

        @include('rekap.partials.filter', ['action' => route('kepala-bps.rekap')])

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
                'ketPembinaan' => fn ($p) => $p['bagian'] . ' · ' . ($p['total'] === 0 ? 'belum melapor' : $p['hari_lapor'] . ' hari lapor'),
            ])

            @include('rekap.partials.pengalih-tab')

            {{-- Per pegawai --}}
            <div x-show="tab === 'pegawai'" class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 font-medium w-12">#</th>
                                <th class="py-3 px-4 font-medium">Pegawai</th>
                                <th class="py-3 px-4 font-medium hidden md:table-cell">Kehadiran lapor</th>
                                <th class="py-3 px-4 font-medium text-center hidden sm:table-cell">Laporan</th>
                                <th class="py-3 px-4 font-medium text-center hidden lg:table-cell">Disetujui</th>
                                <th class="py-3 px-4 font-medium text-center hidden lg:table-cell">Dikembalikan</th>
                                <th class="py-3 px-4 font-medium text-center hidden xl:table-cell">Selesai</th>
                                <th class="py-3 px-4 font-medium">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rekap['pegawai'] as $p)
                                <tr x-show="cari === '' || $el.dataset.cari.includes(cari.toLowerCase())"
                                    data-cari="{{ strtolower($p['nama'] . ' ' . $p['bagian']) }}"
                                    class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-4 text-gray-400 font-medium">
                                        {{ $p['total'] > 0 || $p['skor'] !== null ? $p['peringkat'] : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @include('rekap.partials.sel-pegawai', ['nama' => $p['nama'], 'sub' => $p['peran'] . ' · ' . $p['bagian']])
                                    </td>
                                    <td class="py-3 px-4 hidden md:table-cell">
                                        <div class="flex items-center gap-2 min-w-[8rem]">
                                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-[#1F3864]" style="width: {{ $p['disiplin'] ?? 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 w-14 text-right">
                                                {{ $p['hari_lapor'] }}/{{ $rekap['periode']['hari_kerja'] }} hari
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700 hidden sm:table-cell">{{ $p['total'] }}</td>
                                    <td class="py-3 px-4 text-center text-green-600 hidden lg:table-cell">{{ $p['disetujui'] }}</td>
                                    <td class="py-3 px-4 text-center hidden lg:table-cell {{ $p['dikembalikan'] ? 'text-red-600' : 'text-gray-400' }}">{{ $p['dikembalikan'] }}</td>
                                    <td class="py-3 px-4 text-center text-gray-600 hidden xl:table-cell">
                                        {{ $p['hasil'] !== null ? round($p['hasil']) . '%' : '-' }}
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
                            ['Pegawai', $b['jumlah_pegawai'], 'text-gray-900'],
                            ['Laporan', $b['total'], 'text-gray-900'],
                            ['Disetujui', $b['disetujui'], 'text-green-600'],
                            ['Belum lapor', $b['belum_lapor'], $b['belum_lapor'] ? 'text-amber-600' : 'text-gray-400'],
                        ],
                        'tautan' => route('kepala-bps.rekap', ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $b['id'], 'tab' => 'pegawai']),
                    ])
                @endforeach
            </div>

            @include('rekap.partials.cara-hitung', [
                'pembuka' => 'Skor 0–100 dari tiga komponen, berdasarkan laporan harian yang sudah dikirim (draft tidak dihitung):',
                'komponen' => [
                    ['Kedisiplinan', Rekap::BOBOT['disiplin'], 'hari yang ada laporannya dibagi hari kerja (Senin–Jumat) pada periode ini.'],
                    ['Kualitas', Rekap::BOBOT['kualitas'], 'laporan disetujui dibagi laporan yang sudah diproses atasan (disetujui + dikembalikan).'],
                    ['Hasil kerja', Rekap::BOBOT['hasil'], 'laporan dengan output "selesai" dibagi seluruh laporan.'],
                ],
                'catatan' => [
                    'Komponen yang belum ada datanya (misalnya belum ada laporan yang diproses atasan) dilewati dan bobotnya dibagi ulang. Hari libur nasional dan cuti belum diperhitungkan. Skor bagian adalah rata-rata skor seluruh anggotanya.',
                ],
            ])
        @endif
    </div>
</x-app-layout>