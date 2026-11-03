@php
    $user = auth()->user();
    // Kepala BPS, admin, dan Ketua Tim (mode ketua) = pengawas.
    // Anggota/staf, termasuk ketua yang sedang di mode Anggota = pelaksana.
    $pengawas = $user->isKepalaBps() || $user->isAdmin() || \App\Support\ModeKerja::ketuaAktif($user);
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 space-y-5">
        <div class="flex flex-wrap justify-between gap-4 items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pekerjaan & disposisi</h1>
                <p class="text-sm text-slate-500">Penugasan berjenjang dengan jejak input, proses, dan output.</p>
            </div>
            @if (\App\Support\AksesPekerjaan::bolehDisposisi(auth()->user()))
                @if (auth()->user()->isKepalaBps() || auth()->user()->isAdmin())
                    <a href="{{ route('sop.index') }}"
                        class="border border-blue-800 text-blue-900 bg-white rounded-lg px-4 py-2 text-sm font-semibold">Delegasikan
                        SOP ke Ketua Tim</a>
                @endif
                <a href="{{ route('pekerjaan.create') }}"
                    class="bg-[#1F3864] text-white rounded-lg px-4 py-2 text-sm font-semibold">+ Pekerjaan / disposisi
                    baru</a>
            @endif
        </div>

        @include('dashboard.partials.pengingat-laporan')

        <form class="bg-white border rounded-xl p-4 flex flex-wrap gap-3" method="GET">
            @if ($hanyaTerlambat)
                <input type="hidden" name="terlambat" value="1">
            @endif
            <input name="cari" value="{{ request('cari') }}" placeholder="Cari judul pekerjaan"
                class="flex-1 min-w-48 rounded-lg border-slate-300 text-sm">
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua status</option>
                @foreach (['belum_dikerjakan' => 'Belum dikerjakan', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'] as $v => $s)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-800 text-white px-4 py-2 text-sm">Filter</button>
        </form>

        {{-- Tombol pekerjaan melewati tenggat --}}
        <a href="{{ $hanyaTerlambat ? route('pekerjaan.index') : route('pekerjaan.index', ['terlambat' => 1]) }}"
            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold transition
        {{ $hanyaTerlambat
            ? 'bg-red-600 text-white hover:bg-red-700'
            : 'bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50' }}">
            <span class="h-2 w-2 rounded-full {{ $hanyaTerlambat ? 'bg-white' : 'bg-red-500' }}"></span>
            Lewat tenggat
            <span
                class="text-xs tabular-nums {{ $hanyaTerlambat ? 'text-white/80' : 'text-red-400' }}">{{ $jumlahTerlambat }}</span>
        </a>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="p-3 font-medium">Pekerjaan / Disposisi</th>
                        <th class="p-3 font-medium">Tim</th>
                        <th class="p-3 font-medium">Pelaksana</th>
                        <th class="p-3 font-medium">Tenggat</th>
                        <th class="p-3 font-medium">Status Pekerjaan</th>
                        <th class="p-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tugas as $i => $item)
                        @php
                            $badge = match ($item->status) {
                                'selesai' => [
                                    'bg-green-50 text-green-700 ring-green-600/20',
                                    'bg-green-500',
                                    'Selesai',
                                ],
                                'dikerjakan' => [
                                    'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'bg-blue-500',
                                    'Dikerjakan',
                                ],
                                default => [
                                    'bg-gray-100 text-gray-600 ring-gray-500/20',
                                    'bg-gray-400',
                                    'Belum dikerjakan',
                                ],
                            };
                            $terlambat =
                                $item->tenggat &&
                                $item->status !== 'selesai' &&
                                $item->tenggat->isPast() &&
                                !$item->tenggat->isToday();
                            $hariTerlambat = $terlambat ? (int) $item->tenggat->diffInDays(today(), true) : 0;
                            $pesanTerlambat = $pengawas
                                ? 'Tenggat sudah lewat dan belum selesai. Tindak lanjuti ke ' .
                                    ($item->penerimaTugas?->name ?? 'pelaksana') .
                                    '.'
                                : 'Tenggat sudah lewat. Pekerjaan ini harus segera diselesaikan.';
                        @endphp
                        <tr class="animate-row group hover:bg-blue-50/40 transition-colors"
                            style="animation-delay: {{ min($i, 10) * 0.04 }}s">
                            <td class="p-3">
                                <div class="font-medium text-gray-900">
                                    {!! nl2br(e(Str::wordWrap($item->judul, 40, "\n", true))) !!}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $item->induk ? '↳ Disposisi dari: ' . $item->induk->judul : 'Pekerjaan utama' }}
                                </div>
                            </td>
                            <td class="p-3 text-gray-600">{{ $item->bagian?->nama_bagian }}</td>
                            <td class="p-3 text-gray-600">{{ $item->penerimaTugas?->name }}</td>
                            <td class="p-3 whitespace-nowrap tabular-nums">
                                @if ($terlambat)
                                    <div tabindex="0"
                                        class="group/tenggat relative inline-block cursor-help outline-none">
                                        <span class="inline-flex items-center gap-1 font-semibold text-red-600">
                                            {{ $item->tenggat->format('d/m/Y') }}
                                        </span>
                                        <span class="block text-[11px] font-medium text-red-500">Terlambat
                                            {{ $hariTerlambat }} hari</span>

                                        <span role="tooltip"
                                            class="pointer-events-none invisible absolute bottom-full left-0 z-20 mb-2 w-60 translate-y-1 whitespace-normal rounded-lg bg-gray-900 px-3 py-2 text-left text-xs font-normal leading-snug text-white opacity-0 shadow-lg transition duration-200 group-hover/tenggat:visible group-hover/tenggat:translate-y-0 group-hover/tenggat:opacity-100 group-focus/tenggat:visible group-focus/tenggat:translate-y-0 group-focus/tenggat:opacity-100 motion-reduce:transition-none">
                                            {{ $pesanTerlambat }}
                                            <span
                                                class="absolute left-4 top-full -mt-1 h-2 w-2 rotate-45 bg-gray-900"></span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-600">{{ $item->tenggat?->format('d/m/Y') ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap {{ $badge[0] }} text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>{{ $badge[2] }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-right">
                                <a href="{{ route('pekerjaan.show', $item) }}"
                                    class="inline-flex items-center gap-1 font-semibold text-[#1F3864]">
                                    Detail
                                    <span
                                        class="transition-transform group-hover:translate-x-1 motion-reduce:transition-none">→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            {{ $hanyaTerlambat ? 'Tidak ada pekerjaan yang melewati tenggat. 🎉' : 'Belum ada pekerjaan yang dapat dilihat.' }}
                        </td>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tugas->links() }}</div>
    </div>
    </x-app-layout>
