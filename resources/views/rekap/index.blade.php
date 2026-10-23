{{-- resources/views/rekap/index.blade.php --}}
@php
    use App\Services\RekapKinerjaService as Rekap;

    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

    $gaya = [
        'sangat_baik' => ['badge' => 'bg-green-50 text-green-700 ring-green-600/20', 'batang' => 'bg-green-500', 'teks' => 'text-green-600'],
        'baik' => ['badge' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'batang' => 'bg-blue-500', 'teks' => 'text-blue-600'],
        'cukup' => ['badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'batang' => 'bg-amber-500', 'teks' => 'text-amber-600'],
        'kurang' => ['badge' => 'bg-red-50 text-red-700 ring-red-600/20', 'batang' => 'bg-red-500', 'teks' => 'text-red-600'],
        'kosong' => ['badge' => 'bg-gray-100 text-gray-600 ring-gray-500/20', 'batang' => 'bg-gray-300', 'teks' => 'text-gray-400'],
    ];

    $r = $rekap['ringkasan'];
    $parameterUnduh = ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $bagianId];
    $bagianDipilih = $daftarBagian->firstWhere('id', $bagianId);
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
        x-data="{ tab: @js(request('tab') === 'bagian' ? 'bagian' : 'pegawai'), cari: '' }">

        {{-- Judul + tombol unduh --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Rekap Kinerja Kantor</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Periode <span class="font-medium text-gray-700">{{ $rekap['periode']['label'] }}</span>
                    &middot; {{ $rekap['periode']['hari_kerja'] }} hari kerja
                    @if ($bagianDipilih)
                        &middot; {{ $bagianDipilih->nama_bagian }}
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kepala-bps.rekap.export-excel', $parameterUnduh) }}"
                    class="inline-flex items-center gap-2 bg-white hover:bg-green-50 text-green-700 border border-green-300 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Excel
                </a>
                <a href="{{ route('kepala-bps.rekap.export-pdf', $parameterUnduh) }}"
                    class="inline-flex items-center gap-2 bg-white hover:bg-red-50 text-red-700 text-sm border border-red-300 font-medium px-4 py-2.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh PDF
                </a>
            </div>
        </div>

        {{-- Filter periode (otomatis, tanpa tombol terapkan) --}}
        <form method="GET" action="{{ route('kepala-bps.rekap') }}"
            class="bg-white rounded-xl border border-gray-200 p-3 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <input type="hidden" name="tab" :value="tab">

            <select name="bulan" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                @foreach ($namaBulan as $angka => $nama)
                    <option value="{{ $angka }}" @selected($bulan === $angka)>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="tahun" onchange="this.form.submit()"
                class="text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                @for ($t = now()->year + 1; $t >= now()->year - 5; $t--)
                    <option value="{{ $t }}" @selected($tahun === $t)>{{ $t }}</option>
                @endfor
            </select>

            <select name="bagian" onchange="this.form.submit()"
                class="col-span-2 text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                <option value="">Semua bagian</option>
                @foreach ($daftarBagian as $b)
                    <option value="{{ $b->id }}" @selected($bagianId === $b->id)>{{ $b->nama_bagian }}</option>
                @endforeach
            </select>
        </form>

        @if ($rekap['pegawai']->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16 px-4">
                <p class="text-sm font-medium text-gray-900">Tidak ada pegawai pada filter ini</p>
                <p class="text-sm text-gray-500 mt-1">Pilih bagian lain atau reset filter.</p>
            </div>
        @else

            {{-- Kartu ringkasan --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-500">Rata-rata skor</p>
                    @php $tingkatRata = Rekap::tingkat($r['rata_skor'], max($r['total_laporan'], $r['rata_skor'] !== null ? 1 : 0)); @endphp
                    <p class="text-3xl font-semibold mt-2 {{ $gaya[$tingkatRata]['teks'] }}">
                        {{ $r['rata_skor'] ?? '-' }}
                    </p>
                    <span class="inline-flex mt-2 text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset {{ $gaya[$tingkatRata]['badge'] }}">
                        {{ Rekap::LABEL[$tingkatRata] }}
                    </span>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-500">Pegawai sudah melapor</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2">
                        {{ $r['melapor'] }}<span class="text-base text-gray-400 font-normal"> / {{ $r['jumlah_pegawai'] }}</span>
                    </p>
                    <p class="text-xs mt-2 {{ $r['belum_lapor'] ? 'text-amber-600' : 'text-gray-400' }}">
                        {{ $r['belum_lapor'] ? $r['belum_lapor'] . ' orang belum melapor' : 'Semua sudah melapor' }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-500">Total laporan</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2">{{ $r['total_laporan'] }}</p>
                    <p class="text-xs text-gray-400 mt-2">Tidak termasuk draft</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs text-gray-500">Tingkat persetujuan</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2">
                        {{ $r['tingkat_persetujuan'] !== null ? $r['tingkat_persetujuan'] . '%' : '-' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-2">Disetujui dari yang sudah diproses</p>
                </div>
            </div>

            {{-- Terbaik dan perlu pembinaan --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </span>
                        <h2 class="font-semibold text-gray-900">Kinerja terbaik</h2>
                    </div>
                    <ul class="space-y-3">
                        @forelse ($r['terbaik'] as $p)
                            <li class="flex items-center gap-3">
                                <span class="w-6 text-center text-sm font-semibold text-gray-400">{{ $p['peringkat'] }}</span>
                                <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($p['nama'], 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $p['nama'] }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $p['bagian'] }}</p>
                                </div>
                                <span class="text-sm font-semibold {{ $gaya[$p['tingkat']]['teks'] }}">{{ $p['skor'] }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400 py-4 text-center">Belum ada laporan pada periode ini.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </span>
                        <h2 class="font-semibold text-gray-900">Perlu pembinaan</h2>
                        <span class="text-xs text-gray-400">skor di bawah 70</span>
                    </div>
                    <ul class="space-y-3">
                        @forelse ($r['pembinaan'] as $p)
                            <li class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($p['nama'], 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $p['nama'] }}</p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $p['bagian'] }} &middot;
                                        {{ $p['total'] === 0 ? 'belum melapor' : $p['hari_lapor'] . ' hari lapor' }}
                                    </p>
                                </div>
                                <span class="text-sm font-semibold {{ $gaya[$p['tingkat']]['teks'] }}">{{ $p['skor'] }}</span>
                            </li>
                        @empty
                            <li class="flex items-center justify-center gap-2 text-sm text-green-700 py-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tidak ada pegawai di bawah skor 70.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Tab --}}
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="inline-flex bg-gray-100 rounded-lg p-1">
                    <button type="button" @click="tab = 'pegawai'"
                        :class="tab === 'pegawai' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors">
                        Per pegawai
                    </button>
                    <button type="button" @click="tab = 'bagian'"
                        :class="tab === 'bagian' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors">
                        Per bagian
                    </button>
                </div>

                <div x-show="tab === 'pegawai'" x-cloak class="relative w-full max-w-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="text" x-model="cari" placeholder="Cari nama pegawai..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
                </div>
            </div>

            {{-- Tabel per pegawai --}}
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
                                @php $g = $gaya[$p['tingkat']]; @endphp
                                <tr x-show="cari === '' || $el.dataset.cari.includes(cari.toLowerCase())"
                                    data-cari="{{ strtolower($p['nama'] . ' ' . $p['bagian']) }}"
                                    class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-4 text-gray-400 font-medium">
                                        {{ $p['total'] > 0 || $p['skor'] !== null ? $p['peringkat'] : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($p['nama'], 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-900 truncate">{{ $p['nama'] }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $p['peran'] }} &middot; {{ $p['bagian'] }}</p>
                                            </div>
                                        </div>
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
                                        <div class="flex items-center gap-3 min-w-[9rem]">
                                            <div class="w-14">
                                                <p class="text-base font-semibold {{ $g['teks'] }}">{{ $p['skor'] ?? '-' }}</p>
                                                <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-1">
                                                    <div class="h-full rounded-full {{ $g['batang'] }}" style="width: {{ $p['skor'] ?? 0 }}%"></div>
                                                </div>
                                            </div>
                                            <span class="inline-flex text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset whitespace-nowrap {{ $g['badge'] }}">
                                                {{ Rekap::LABEL[$p['tingkat']] }}
                                            </span>
                                        </div>
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
                    @php $g = $gaya[$b['tingkat']]; @endphp
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Peringkat {{ $i + 1 }}</p>
                                <h3 class="font-semibold text-gray-900 truncate">{{ $b['nama'] }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">Kepala tim: {{ $b['kepala'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-2xl font-semibold {{ $g['teks'] }}">{{ $b['skor'] ?? '-' }}</p>
                                <span class="inline-flex text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset {{ $g['badge'] }}">
                                    {{ Rekap::LABEL[$b['tingkat']] }}
                                </span>
                            </div>
                        </div>

                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mt-4">
                            <div class="h-full rounded-full {{ $g['batang'] }}" style="width: {{ $b['skor'] ?? 0 }}%"></div>
                        </div>

                        <dl class="grid grid-cols-4 gap-2 mt-4 text-center">
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Pegawai</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $b['jumlah_pegawai'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Laporan</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $b['total'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Disetujui</dt>
                                <dd class="text-sm font-semibold text-green-600">{{ $b['disetujui'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-gray-400">Belum lapor</dt>
                                <dd class="text-sm font-semibold {{ $b['belum_lapor'] ? 'text-amber-600' : 'text-gray-400' }}">{{ $b['belum_lapor'] }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('kepala-bps.rekap', ['bulan' => $bulan, 'tahun' => $tahun, 'bagian' => $b['id'], 'tab' => 'pegawai']) }}"
                            class="inline-flex items-center gap-1 mt-4 text-xs font-medium text-[#1F3864] hover:underline">
                            Lihat pegawai bagian ini
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Cara perhitungan --}}
            <details class="bg-white rounded-xl border border-gray-200 p-5 group">
                <summary class="cursor-pointer text-sm font-medium text-gray-700 list-none flex items-center justify-between">
                    Bagaimana skor dihitung?
                    <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </summary>
                <div class="mt-4 text-sm text-gray-600 space-y-3">
                    <p>Skor 0&ndash;100 dari tiga komponen, berdasarkan laporan harian yang sudah dikirim (draft tidak dihitung):</p>
                    <ul class="space-y-2">
                        <li><span class="font-medium text-gray-900">Kedisiplinan ({{ Rekap::BOBOT['disiplin'] }}%)</span> &ndash; hari yang ada laporannya dibagi hari kerja (Senin&ndash;Jumat) pada periode ini.</li>
                        <li><span class="font-medium text-gray-900">Kualitas ({{ Rekap::BOBOT['kualitas'] }}%)</span> &ndash; laporan disetujui dibagi laporan yang sudah diproses atasan (disetujui + dikembalikan).</li>
                        <li><span class="font-medium text-gray-900">Hasil kerja ({{ Rekap::BOBOT['hasil'] }}%)</span> &ndash; laporan dengan output &ldquo;selesai&rdquo; dibagi seluruh laporan.</li>
                    </ul>
                    <p class="text-xs text-gray-500">
                        Komponen yang belum ada datanya (misalnya belum ada laporan yang diproses atasan) dilewati dan bobotnya dibagi ulang.
                        Hari libur nasional dan cuti belum diperhitungkan. Skor bagian adalah rata-rata skor seluruh anggotanya.
                    </p>
                    <p class="text-xs text-gray-500">
                        Predikat: Sangat Baik &ge; 85 &middot; Baik &ge; 70 &middot; Cukup &ge; 55 &middot; Perlu Perhatian &lt; 55.
                    </p>
                </div>
            </details>
        @endif
    </div>
</x-app-layout>