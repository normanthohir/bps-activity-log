@php
    $adaFilter = collect(['cari', 'status', 'periode', 'bagian'])->contains(fn($k) => request()->filled($k));
    $kelasInput = 'w-full text-sm rounded-lg border-gray-300 focus:ring-[#1F3864]/20 focus:border-[#1F3864]';

    $statusAktif = request('status');
    $total = $ringkasan->sum();

    $labelPeriode = 'Semua periode';
    if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) request('periode'), $m)) {
        $labelPeriode = \Carbon\Carbon::create((int) $m[1], (int) $m[2], 1)->translatedFormat('F Y');
    }

    // Klik kartu status untuk memfilter, klik lagi untuk melepas filter
    $urlStatus = fn($s) => request()->fullUrlWithQuery(['status' => $statusAktif === $s ? null : $s, 'page' => null]);

    $kartu = [
        [
            'label' => 'Menunggu',
            'nilai' => $ringkasan['menunggu'],
            'ket' => $ringkasan['menunggu'] > 0 ? 'Perlu diproses' : 'Semua sudah diproses',
            'url' => $urlStatus('menunggu'),
            'dipilih' => $statusAktif === 'menunggu',
            'aktif' => $ringkasan['menunggu'] > 0,
            'garis' => 'from-amber-500 to-yellow-300',
            'ikon' => 'from-amber-400 to-amber-500 shadow-amber-500/30',
            'blob' => 'bg-amber-50',
            'hover' => 'hover:border-amber-300',
            'ring' => 'ring-2 ring-amber-300',
            'angka' => $ringkasan['menunggu'] > 0 ? 'text-amber-600' : 'text-gray-900',
            'svg' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        [
            'label' => 'Disetujui',
            'nilai' => $ringkasan['disetujui'],
            'ket' => 'Sudah disetujui',
            'url' => $urlStatus('disetujui'),
            'dipilih' => $statusAktif === 'disetujui',
            'garis' => 'from-green-500 to-emerald-300',
            'ikon' => 'from-green-500 to-green-600 shadow-green-500/30',
            'blob' => 'bg-green-50',
            'hover' => 'hover:border-green-300',
            'ring' => 'ring-2 ring-green-300',
            'angka' => 'text-gray-900',
            'svg' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        [
            'label' => 'Dikembalikan',
            'nilai' => $ringkasan['dikembalikan'],
            'ket' => $ringkasan['dikembalikan'] > 0 ? 'Menunggu revisi pegawai' : 'Tidak ada yang dikembalikan',
            'url' => $urlStatus('dikembalikan'),
            'dipilih' => $statusAktif === 'dikembalikan',
            'garis' => 'from-red-500 to-rose-300',
            'ikon' => 'from-red-500 to-red-600 shadow-red-500/30',
            'blob' => 'bg-red-50',
            'hover' => 'hover:border-red-300',
            'ring' => 'ring-2 ring-red-300',
            'angka' => $ringkasan['dikembalikan'] > 0 ? 'text-red-600' : 'text-gray-900',
            'svg' => 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3',
        ],
        [
            'label' => 'Semua Laporan',
            'nilai' => $total,
            'ket' => 'Tidak termasuk draft',
            'url' => request()->fullUrlWithQuery(['status' => null, 'page' => null]),
            'dipilih' => !$statusAktif,
            'garis' => 'from-indigo-500 to-violet-300',
            'ikon' => 'from-indigo-500 to-indigo-600 shadow-indigo-500/30',
            'blob' => 'bg-indigo-50',
            'hover' => 'hover:border-indigo-300',
            'ring' => 'ring-2 ring-indigo-300',
            'angka' => 'text-gray-900',
            'svg' =>
                'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        ],
    ];
@endphp

<x-app-layout>
    {{-- <style>
        @keyframes bps-up {
            from {
                opacity: 0;
                transform: translateY(18px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes bps-grow {
            from {
                transform: scaleX(0)
            }

            to {
                transform: scaleX(1)
            }
        }

        @keyframes bps-ping {
            0% {
                box-shadow: 0 0 0 0 rgb(245 158 11 / .55)
            }

            100% {
                box-shadow: 0 0 0 12px rgb(245 158 11 / 0)
            }
        }

        @keyframes bps-float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-6px)
            }
        }

        @keyframes bps-shine {
            from {
                background-position: -200% 0
            }

            to {
                background-position: 200% 0
            }
        }

        @keyframes bps-pop {
            0% {
                transform: scale(.6);
                opacity: 0
            }

            70% {
                transform: scale(1.08)
            }

            100% {
                transform: scale(1);
                opacity: 1
            }
        }

        @keyframes bps-spin {
            from {
                transform: rotate(-90deg) scale(.7);
                opacity: 0
            }

            to {
                transform: rotate(0) scale(1);
                opacity: 1
            }
        }

        .bps .fx {
            opacity: 0;
            animation: bps-up .6s cubic-bezier(.2, .7, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        .bps .fx-bar {
            transform-origin: left;
            animation: bps-grow 1s .5s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .fx-pop {
            animation: bps-pop .5s cubic-bezier(.2, .7, .2, 1) both;
            animation-delay: var(--d, 0s);
        }

        .bps .fx-ring {
            animation: bps-spin .9s .4s cubic-bezier(.2, .7, .2, 1) both;
        }

        .bps .ping {
            animation: bps-ping 1.8s ease-out infinite;
        }

        .bps .float {
            animation: bps-float 4s ease-in-out infinite;
        }

        .bps .shine {
            background: linear-gradient(110deg, transparent 30%, rgb(255 255 255 / .8) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: bps-shine 3s linear infinite;
        }

        .bps .stat {
            transition: transform .3s, box-shadow .3s, border-color .3s;
        }

        .bps .stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px -14px rgb(31 56 100 / .35);
        }

        .bps .stat .blob {
            transition: transform .6s;
        }

        .bps .stat:hover .blob {
            transform: scale(1.8);
        }

        .bps .stat .icon {
            transition: transform .35s;
        }

        .bps .stat:hover .icon {
            transform: rotate(-8deg) scale(1.12);
        }

        /* Kalender: hanya mempercantik tampilan, logika tidak diubah */
        .bps .rapi-calendar-day:not(.outside) {
            transition: background .2s;
        }

        .bps .rapi-calendar-day:not(.outside):hover {
            background: #f1f5f9;
        }

        .bps .rapi-calendar-day.outside {
            background: repeating-linear-gradient(135deg, #f8fafc, #f8fafc 6px, #f1f5f9 6px, #f1f5f9 12px);
        }

        .bps .rapi-calendar-date.today {
            background: linear-gradient(135deg, #1f3864, #355b91);
            box-shadow: 0 4px 10px -2px rgb(31 56 100 / .5);
        }

        .bps .rapi-calendar-event {
            border-radius: 7px;
            box-shadow: 0 1px 2px rgb(15 23 42 / .08);
            transition: transform .2s, box-shadow .2s;
        }

        .bps .rapi-calendar-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px -4px rgb(15 23 42 / .25);
            text-decoration: none;
            filter: none;
        }

        .bps .rapi-calendar-event.belum_dikerjakan {
            background: linear-gradient(90deg, #fde68a, #fef3c7);
        }

        .bps .rapi-calendar-event.dikerjakan {
            background: linear-gradient(90deg, #bfdbfe, #dbeafe);
        }

        .bps .rapi-calendar-event.selesai {
            background: linear-gradient(90deg, #a7f3d0, #d1fae5);
        }

        @media (prefers-reduced-motion: reduce) {

            .bps *,
            .bps *::before {
                animation: none !important;
                transition: none !important;
            }

            .bps .fx,
            .bps .fx-pop,
            .bps .fx-ring {
                opacity: 1;
            }
        }
    </style> --}}

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="animate-in mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">
                    {{ $semuaBagian ? 'Persetujuan Laporan' : 'Persetujuan Laporan Tim' }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $semuaBagian
                        ? 'Tinjau, setujui, atau kembalikan laporan harian dari seluruh staf dan Ketua Tim.'
                        : 'Tinjau, setujui, atau kembalikan laporan harian dari anggota tim Anda.' }}
                    Buka <span class="font-medium">Detail</span> untuk memprosesnya.
                </p>
            </div>

            <span
                class="inline-flex items-center gap-2 self-start sm:self-auto rounded-full bg-[#DCE6F1] text-[#1F3864] text-xs sm:text-sm font-medium px-3 py-1.5">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                {{ $cakupan }}
            </span>
        </div>

        
        {{-- Ringkasan status (klik untuk memfilter, klik lagi untuk melepas) --}}
        {{-- <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
            @foreach ($kartu as $i => $k)
                <a href="{{ $k['url'] }}"
                    class="fx stat group relative block overflow-hidden bg-white border border-gray-200 {{ $k['hover'] }} rounded-2xl p-4 sm:p-5 {{ !empty($k['dipilih']) ? $k['ring'] : '' }}"
                    style="--d:{{ 0.15 + $i * 0.08 }}s">
                    <span class="blob absolute -right-6 -bottom-6 w-24 h-24 rounded-full {{ $k['blob'] }}"></span>
                    <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $k['garis'] }}"></span>
                    @if (!empty($k['aktif']))
                        <span class="shine absolute inset-x-0 top-0 h-1"></span>
                    @endif

                    @if (!empty($k['dipilih']))
                        <span
                            class="absolute right-3 top-3 text-[10px] font-semibold text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">Terpilih</span>
                    @endif

                    <div class="relative">
                        <div
                            class="icon w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br {{ $k['ikon'] }} text-white shadow-md flex items-center justify-center {{ !empty($k['aktif']) ? 'ping' : '' }}">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['svg'] }}" />
                            </svg>
                        </div>
                        <p class="fx-pop text-3xl sm:text-4xl font-bold tabular-nums mt-3 leading-none {{ $k['angka'] }}"
                            style="--d:{{ 0.3 + $i * 0.08 }}s">{{ $k['nilai'] }}</p>
                        <p class="text-sm font-medium text-gray-700 mt-1.5 truncate">{{ $k['label'] }}</p>
                        <p class="text-[11px] sm:text-xs text-gray-400 truncate">{{ $k['ket'] }}</p>
                    </div>
                </a>
            @endforeach
        </div> --}}

        {{-- Filter --}}
        <form method="GET" action="{{ route($rute . '.index') }}"
            class="animate-in delay-1 bg-white rounded-xl border border-gray-200 p-4 mb-5">
            <div class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <label class="text-xs text-gray-500 block mb-1">Cari</label>
                    <input type="text" name="cari" value="{{ request('cari') }}"
                        placeholder="Nama pegawai, pekerjaan, atau uraian..." class="{{ $kelasInput }}">
                </div>

                @if ($semuaBagian)
                    <div class="md:w-44">
                        <label class="text-xs text-gray-500 block mb-1">Bagian</label>
                        <select name="bagian" class="{{ $kelasInput }}">
                            <option value="">Semua bagian</option>
                            @foreach ($daftarBagian as $b)
                                <option value="{{ $b->id }}" @selected((string) request('bagian') === (string) $b->id)>{{ $b->nama_bagian }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="md:w-40">
                    <label class="text-xs text-gray-500 block mb-1">Status</label>
                    <select name="status" class="{{ $kelasInput }}">
                        <option value="">Semua status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                    </select>
                </div>

                <div class="md:w-40">
                    <label class="text-xs text-gray-500 block mb-1">Bulan - Tahun</label>
                    <input type="month" name="periode" value="{{ request('periode') }}" class="{{ $kelasInput }}">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm transition-colors">
                        Filter
                    </button>
                    @if ($adaFilter)
                        <a href="{{ route($rute . '.index') }}"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="animate-in delay-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if ($laporan->isEmpty())
                <div class="text-center py-16 px-4">
                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if ($adaFilter)
                        <p class="text-sm font-medium text-gray-900 mt-3">Tidak ada laporan yang cocok dengan filter ini
                        </p>
                        <a href="{{ route($rute . '.index') }}"
                            class="inline-block mt-3 text-sm text-[#1F3864] font-medium hover:underline">Reset
                            filter</a>
                    @else
                        <p class="text-sm font-medium text-gray-900 mt-3">Belum ada laporan masuk</p>
                        <p class="text-sm text-gray-500 mt-1">Laporan yang sudah dikirim pegawai akan muncul di sini.
                        </p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-5 font-medium">Pegawai</th>
                                <th class="py-3 px-5 font-medium">Tanggal</th>
                                <th class="py-3 px-5 font-medium">Pekerjaan / uraian</th>
                                <th class="py-3 px-5 font-medium">Status</th>
                                <th class="py-3 px-5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($laporan as $i => $item)
                                @php $judul = $item->tugas?->judul ?? $item->uraian ?? '-'; @endphp
                                <tr class="animate-row hover:bg-gray-50/70 transition-colors"
                                    style="animation-delay: {{ min($i, 10) * 0.04 }}s">
                                    <td class="py-3 px-5">
                                        <a href="{{ route($rute . '.show', $item) }}"
                                            class="font-medium text-gray-900 hover:text-[#1F3864] hover:underline">
                                            {{ $item->user->name }}
                                        </a>
                                        <p class="text-xs text-gray-500">{{ $item->user->bagian?->nama_bagian ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="py-3 px-5 text-gray-500 whitespace-nowrap">
                                        {{ $item->tanggal->format('d/m/Y') }}</td>
                                    <td class="py-3 px-5 text-gray-700">
                                        <p class="max-w-md break-words" title="{{ $judul }}">
                                            {{ Str::limit($judul, 90) }}</p>
                                        @if ($item->tugas)
                                            <p class="text-xs text-gray-400">Terkait tugas</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-5"><x-status-badge :status="$item->status" /></td>
                                    <td class="py-3 px-5 text-right">
                                        <a href="{{ route($rute . '.show', $item) }}"
                                            class="text-blue-700 font-semibold whitespace-nowrap">
                                            Detail →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($laporan->hasPages())
                    <div class="border-t border-gray-100 px-5 py-3">{{ $laporan->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
