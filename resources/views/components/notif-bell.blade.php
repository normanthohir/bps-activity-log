
@props(['notif'])

@php
    $tones = [
        'red' => [
            'head' => 'border-red-100 bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
            'btn' => 'bg-red-600 hover:bg-red-700',
            'badge' => 'bg-red-50 text-red-700 ring-red-600/20',
            'row' => 'hover:bg-red-50/50',
        ],
        'orange' => [
            'head' => 'border-orange-100 bg-orange-50 text-orange-700',
            'dot' => 'bg-orange-500',
            'btn' => 'bg-orange-600 hover:bg-orange-700',
            'badge' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
            'row' => 'hover:bg-orange-50/50',
        ],
        'green' => [
            'head' => 'border-green-100 bg-green-50 text-green-700',
            'dot' => 'bg-green-500',
            'btn' => 'bg-green-600 hover:bg-green-700',
            'badge' => 'bg-green-50 text-green-700 ring-green-600/20',
            'row' => 'hover:bg-green-50/50',
        ],
        'amber' => [
            'head' => 'border-amber-100 bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
            'btn' => 'bg-amber-600 hover:bg-amber-700',
            'badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'row' => 'hover:bg-amber-50/50',
        ],
        'sky' => [
            'head' => 'border-sky-100 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
            'btn' => 'bg-sky-600 hover:bg-sky-700',
            'badge' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            'row' => 'hover:bg-sky-50/50',
        ],
    ];

    $total = $notif['total'] ?? 0;
    $mendesak = $notif['mendesak'] ?? 0;
    $judulModal = $notif['judul'] ?? 'Pemberitahuan';
    $teksKosong = $notif['kosong'] ?? 'Tidak ada yang perlu ditindaklanjuti.';
    $groups = collect($notif['groups'] ?? [])->filter(fn($g) => !empty($g['items']));
@endphp

<div x-data="{ open: false }" x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open = false">

    <button type="button" @click="open = true" aria-haspopup="dialog"
        aria-label="{{ $judulModal }}, {{ $total }} baru"
        class="group relative rounded-xl p-2 text-gray-500 transition hover:bg-gray-100 hover:text-[#1F3864] active:scale-95 motion-reduce:transition-none">
        <svg class="h-6 w-6 origin-top transition-transform duration-300 group-hover:rotate-12 motion-reduce:transition-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if ($total > 0)
            <span class="absolute right-0.5 top-0.5 flex h-[18px] min-w-[18px]">
                @if ($mendesak > 0)
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-60 motion-reduce:animate-none"></span>
                @endif
                <span
                    class="relative inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full px-1 text-[10px] font-bold tabular-nums text-white ring-2 ring-white {{ $mendesak > 0 ? 'bg-red-500' : 'bg-green-600' }}">
                    {{ $total > 99 ? '99+' : $total }}
                </span>
            </span>
        @endif
    </button>

    {{-- Teleport agar tidak terpengaruh efek blur pada topbar --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak role="dialog" aria-modal="true" aria-labelledby="judul-notif"
            class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4">

            <div x-show="open" x-transition.opacity @click="open = false"
                class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

            <div x-show="open" x-transition
                class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl">

                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 id="judul-notif" class="text-base font-semibold text-gray-900">{{ $judulModal }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ $total > 0 ? $total . ' hal perlu perhatian Anda' : 'Tidak ada yang perlu ditindaklanjuti' }}
                        </p>
                    </div>
                    <button type="button" @click="open = false" aria-label="Tutup"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">

                    @if ($groups->isEmpty())
                        <div class="px-6 py-14 text-center">
                            <span
                                class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-gray-900">Semua beres</p>
                            <p class="text-xs text-gray-500">{{ $teksKosong }}</p>
                        </div>
                    @endif

                    @foreach ($groups as $g)
                        @php $t = $tones[$g['tone'] ?? 'sky'] ?? $tones['sky']; @endphp
                        <section>
                            <h4
                                class="sticky top-0 z-10 flex items-center gap-2 border-y px-5 py-2 text-[11px] font-semibold uppercase tracking-wide {{ $t['head'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $t['dot'] }}"></span>
                                {{ $g['judul'] }}
                            </h4>

                            <ul class="divide-y divide-gray-100">
                                @foreach ($g['items'] as $i)
                                    @if (!empty($i['aksi']))
                                        <li class="flex items-start justify-between gap-3 px-5 py-3.5">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900">{{ $i['judul'] }}</p>
                                                @if (!empty($i['sub']))
                                                    <p class="text-xs text-gray-500">{{ $i['sub'] }}</p>
                                                @endif
                                                @if (!empty($i['catatan']))
                                                    <p class="mt-0.5 line-clamp-2 text-xs text-red-700">{{ $i['catatan'] }}</p>
                                                @endif
                                            </div>
                                            <a href="{{ $i['url'] }}"
                                                class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold text-white transition active:scale-95 {{ $t['btn'] }}">
                                                {{ $i['aksi'] }}
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $i['url'] }}"
                                                class="group flex items-center justify-between gap-3 px-5 py-3.5 transition-colors {{ $t['row'] }}">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-medium text-gray-900">{{ $i['judul'] }}</p>
                                                    @if (!empty($i['sub']))
                                                        <p class="text-xs text-gray-500">{{ $i['sub'] }}</p>
                                                    @endif
                                                </div>
                                                <span
                                                    class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $t['badge'] }}">
                                                    {{ $i['badge'] ?? 'Lihat' }}
                                                    <span
                                                        class="transition-transform group-hover:translate-x-0.5 motion-reduce:transition-none">→</span>
                                                </span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </template>
</div>