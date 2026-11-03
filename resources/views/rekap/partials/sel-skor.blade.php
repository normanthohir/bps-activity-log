{{-- Parameter: $skor, $tingkat, $label (peta predikat) --}}
@php $g = \App\Support\TampilanRekap::gaya($tingkat); @endphp
<div class="flex items-center gap-3 min-w-[9rem]">
    <div class="w-14">
        <p class="text-base font-semibold {{ $g['teks'] }}">{{ $skor ?? '-' }}</p>
        <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-1">
            <div class="h-full rounded-full {{ $g['batang'] }}" style="width: {{ $skor ?? 0 }}%"></div>
        </div>
    </div>
    <span class="inline-flex text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset whitespace-nowrap {{ $g['badge'] }}">
        {{ $label[$tingkat] }}
    </span>
</div>