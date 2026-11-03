{{-- Parameter: $kartu = [['label','nilai','pembilang'?,'nilai_gaya'?,'badge'? => ['teks','gaya'],'sub'?,'sub_gaya'?], ...] --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($kartu as $k)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">{{ $k['label'] }}</p>
            <p class="text-3xl font-semibold mt-2 {{ $k['nilai_gaya'] ?? 'text-gray-900' }}">
                {{ $k['nilai'] }}@isset($k['pembilang'])<span class="text-base text-gray-400 font-normal"> / {{ $k['pembilang'] }}</span>@endisset
            </p>
            @isset($k['badge'])
                <span class="inline-flex mt-2 text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset {{ $k['badge']['gaya'] }}">
                    {{ $k['badge']['teks'] }}
                </span>
            @endisset
            @isset($k['sub'])
                <p class="text-xs mt-2 {{ $k['sub_gaya'] ?? 'text-gray-400' }}">{{ $k['sub'] }}</p>
            @endisset
        </div>
    @endforeach
</div>