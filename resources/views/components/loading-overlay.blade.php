@props(['message' => 'Memproses...'])

{{--
    Overlay loading bertema "bar chart" — merepresentasikan statistik/data,
    sesuai identitas BPS. Dipakai dengan meletakkan komponen ini di dalam
    <form x-data="{ loading: false }" @submit="loading = true"> ... </form>
--}}
<div
    x-cloak
    x-show="loading"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[#0B1B33]/70 backdrop-blur-sm"
>
    <div class="flex flex-col items-center gap-4">
        <div class="flex items-end gap-1.5 h-11">
            <span class="w-2.5 rounded-t-sm bg-white animate-bps-bar" style="animation-delay:0s"></span>
            <span class="w-2.5 rounded-t-sm bg-white animate-bps-bar" style="animation-delay:.15s"></span>
            <span class="w-2.5 rounded-t-sm bg-white animate-bps-bar" style="animation-delay:.3s"></span>
            <span class="w-2.5 rounded-t-sm bg-white animate-bps-bar" style="animation-delay:.45s"></span>
        </div>
        <p class="text-white text-sm font-medium tracking-wide animate-pulse">{{ $message }}</p>
    </div>

    <style>
        @keyframes bps-bar-grow {
            0%, 100% { height: 10px; opacity: .45; }
            50%      { height: 38px; opacity: 1; }
        }
        .animate-bps-bar { animation: bps-bar-grow 1s ease-in-out infinite; }
    </style>
</div>