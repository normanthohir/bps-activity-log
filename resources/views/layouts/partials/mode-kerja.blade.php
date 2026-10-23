@php
    $modeMobile = $modeMobile ?? false;
    $sedangKetua = \App\Support\ModeKerja::ketuaAktif(auth()->user());
    $timDipimpin = auth()->user()->timDipimpin()->orderBy('kode_bagian')->orderBy('nama_bagian')->pluck('nama_bagian');
@endphp
<div class="{{ $modeMobile ? '' : 'px-3 py-3 border-b border-slate-100' }}">
    @unless($modeMobile)
        <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold mb-2">Beralih mode kerja</p>
    @endunless
    <div class="space-y-1">
        <form method="POST" action="{{ route('mode-kerja.ganti') }}">
            @csrf
            <input type="hidden" name="mode" value="anggota">
            <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ !$sedangKetua ? ($modeMobile ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-800 font-semibold') : ($modeMobile ? 'text-white/80 hover:bg-white/10' : 'text-slate-700 hover:bg-slate-50') }}">
                <span>Anggota Tim</span>
                @unless($sedangKetua) <span aria-label="Mode aktif">✓</span> @endunless
            </button>
        </form>
        @if ($timDipimpin->isNotEmpty())
            <form method="POST" action="{{ route('mode-kerja.ganti') }}">
                @csrf
                <input type="hidden" name="mode" value="ketua">
                <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $sedangKetua ? ($modeMobile ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-800 font-semibold') : ($modeMobile ? 'text-white/80 hover:bg-white/10' : 'text-slate-700 hover:bg-slate-50') }}">
                    <span>Ketua Tim</span>
                    @if($sedangKetua) <span aria-label="Mode aktif">✓</span> @endif
                </button>
            </form>
            <p class="px-3 pt-1 text-xs {{ $modeMobile ? 'text-white/70' : 'text-slate-500' }} break-words">Tim dipimpin: {{ $timDipimpin->implode(', ') }}</p>
        @else
            <p class="px-3 text-xs {{ $modeMobile ? 'text-white/70' : 'text-slate-500' }}">Mode Ketua Tim akan tersedia setelah ditetapkan sebagai ketua oleh Admin.</p>
        @endif
    </div>
</div>
