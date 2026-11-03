{{-- Wajib: $kartu = [['label','nilai','warna','ikon', 'url'?, 'ket'?, 'soroti'?], ...]
     warna: blue | indigo | green | amber. Tiga kartu tampil sebaris, empat kartu 2x2 di mobile. --}}
@php
    $palet = [
        'blue'   => ['bar' => 'from-blue-500 to-sky-400',      'ikon' => 'bg-blue-50 text-blue-600',     'hover' => 'hover:border-blue-300'],
        'indigo' => ['bar' => 'from-indigo-500 to-violet-400', 'ikon' => 'bg-indigo-50 text-indigo-600', 'hover' => 'hover:border-indigo-300'],
        'green'  => ['bar' => 'from-green-500 to-emerald-300', 'ikon' => 'bg-green-50 text-green-600',   'hover' => 'hover:border-green-300'],
        'amber'  => ['bar' => 'from-amber-500 to-yellow-300',  'ikon' => 'bg-amber-50 text-amber-600',   'hover' => 'hover:border-amber-300'],
    ];
    $kolom = count($kartu) === 3 ? 'grid-cols-3' : 'grid-cols-2 lg:grid-cols-4';
@endphp

<div class="mb-5 grid gap-2.5 sm:gap-3 {{ $kolom }}">
    @foreach ($kartu as $i => $k)
        @php
            $w = $palet[$k['warna'] ?? 'blue'];
            $tag = !empty($k['url']) ? 'a' : 'div';
            $sorot = !empty($k['soroti']) && (int) $k['nilai'] > 0;
        @endphp

        <{{ $tag }} @if ($tag === 'a') href="{{ $k['url'] }}" @endif
            class="animate-in group relative block overflow-hidden rounded-2xl border border-gray-200 bg-white p-3.5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md sm:p-4 motion-reduce:transition-none {{ $w['hover'] }}"
            style="animation-delay: {{ 0.08 + $i * 0.07 }}s">

            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $w['bar'] }}"></span>

            <div class="flex items-center justify-between gap-2">
                <p class="truncate text-xs font-medium text-gray-500">{{ $k['label'] }}</p>
                <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110 motion-reduce:transition-none {{ $w['ikon'] }}">
                    @if ($sorot)
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-lg bg-amber-400 opacity-30 motion-reduce:animate-none"></span>
                    @endif
                    <svg class="relative h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['ikon'] }}" />
                    </svg>
                </span>
            </div>

            <p class="mt-2 text-2xl font-bold tabular-nums leading-none tracking-tight {{ $sorot ? 'text-amber-600' : 'text-gray-900' }}">
                {{ $k['nilai'] }}
            </p>

            @if (!empty($k['ket']))
                <p class="mt-1 truncate text-[11px] text-gray-400">{{ $k['ket'] }}</p>
            @endif
        </{{ $tag }}>
    @endforeach
</div>