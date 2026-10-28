{{-- @php
    $modeMobile = $modeMobile ?? false;
    $sedangKetua = \App\Support\ModeKerja::ketuaAktif(auth()->user());
    $timDipimpin = auth()->user()->timDipimpin()->orderBy('kode_bagian')->orderBy('nama_bagian')->pluck('nama_bagian');
@endphp
<div class="{{ $modeMobile ? '' : 'px-3 py-3 border-b border-slate-100' }}">
    @unless ($modeMobile)
        <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold mb-2">Beralih mode kerja</p>
    @endunless
    <div class="space-y-1">
        <form method="POST" action="{{ route('mode-kerja.ganti') }}">
            @csrf
            <input type="hidden" name="mode" value="anggota">
            <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ !$sedangKetua ? ($modeMobile ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-800 font-semibold') : ($modeMobile ? 'text-white/80 hover:bg-white/10' : 'text-slate-700 hover:bg-slate-50') }}">
                <span>Anggota Tim</span>
                @unless ($sedangKetua) <span aria-label="Mode aktif">✓</span> @endunless
            </button>
        </form>
        @if ($timDipimpin->isNotEmpty())
            <form method="POST" action="{{ route('mode-kerja.ganti') }}">
                @csrf
                <input type="hidden" name="mode" value="ketua">
                <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $sedangKetua ? ($modeMobile ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-800 font-semibold') : ($modeMobile ? 'text-white/80 hover:bg-white/10' : 'text-slate-700 hover:bg-slate-50') }}">
                    <span>Ketua Tim</span>
                    @if ($sedangKetua) <span aria-label="Mode aktif">✓</span> @endif
                </button>
            </form>
            <p class="px-3 pt-1 text-xs {{ $modeMobile ? 'text-white/70' : 'text-slate-500' }} break-words">Tim dipimpin: {{ $timDipimpin->implode(', ') }}</p>
        @else
            <p class="px-3 text-xs {{ $modeMobile ? 'text-white/70' : 'text-slate-500' }}">Mode Ketua Tim akan tersedia setelah ditetapkan sebagai ketua oleh Admin.</p>
        @endif
    </div>
</div> --}}
@php
    $user = auth()->user();
    $sedangKetua = \App\Support\ModeKerja::ketuaAktif($user);
    $timDipimpin = $user->timDipimpin()->orderBy('kode_bagian')->orderBy('nama_bagian')->pluck('nama_bagian');
    $bisaKetua = $timDipimpin->isNotEmpty();

    $aktif = 'bg-white text-[#1F3864] shadow-sm';
    $biasa = 'text-white/70 hover:text-white hover:bg-white/10';
@endphp

<div x-data="{ loading: false }">
    <p class="px-1 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/45">Mode kerja</p>

    <div class="grid grid-cols-2 gap-1 p-1 rounded-lg bg-black/20" :class="loading ? 'opacity-60' : ''">

        <form method="POST" action="{{ route('mode-kerja.ganti') }}" @submit="loading = true">
            @csrf
            <input type="hidden" name="mode" value="anggota">
            <button type="submit" :disabled="loading"
                class="w-full rounded-md px-2 py-1.5 text-xs font-medium transition-colors disabled:cursor-wait {{ !$sedangKetua ? $aktif : $biasa }}">
                Anggota
            </button>
        </form>

        @if ($bisaKetua)
            <form method="POST" action="{{ route('mode-kerja.ganti') }}" @submit="loading = true">
                @csrf
                <input type="hidden" name="mode" value="ketua">
                <button type="submit" :disabled="loading"
                    class="w-full rounded-md px-2 py-1.5 text-xs font-medium transition-colors disabled:cursor-wait {{ $sedangKetua ? $aktif : $biasa }}">
                    Ketua
                </button>
            </form>
        @else
            <span title="Belum ditetapkan sebagai ketua tim"
                class="w-full text-center rounded-md px-2 py-1.5 text-xs font-medium text-white/30 cursor-not-allowed">
                Ketua
            </span>
        @endif
    </div>

    <p class="mt-2 px-1 text-[11px] leading-snug text-white/55 break-words">
        @if ($bisaKetua)
            Memimpin: {{ $timDipimpin->implode(', ') }}
        @else
            Mode Ketua tersedia setelah Admin menetapkan Anda sebagai ketua tim.
        @endif
    </p>
</div>
