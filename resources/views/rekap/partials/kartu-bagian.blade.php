{{-- Parameter: $no, $b (nama, kepala, skor, tingkat), $label, $stats = [[label, nilai, kelas], ...] (4 item), $tautan --}}
@php $g = \App\Support\TampilanRekap::gaya($b['tingkat']); @endphp
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs text-gray-400">Peringkat {{ $no }}</p>
            <h3 class="font-semibold text-gray-900 truncate">{{ $b['nama'] }}</h3>
            <p class="text-xs text-gray-500 mt-0.5 truncate">Kepala tim: {{ $b['kepala'] }}</p>
        </div>
        <div class="text-right shrink-0">
            <p class="text-2xl font-semibold {{ $g['teks'] }}">{{ $b['skor'] ?? '-' }}</p>
            <span
                class="inline-flex text-[11px] font-medium px-2 py-0.5 rounded-full ring-1 ring-inset {{ $g['badge'] }}">
                {{ $label[$b['tingkat']] }}
            </span>
        </div>
    </div>

    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mt-4">
        <div class="h-full rounded-full {{ $g['batang'] }}" style="width: {{ $b['skor'] ?? 0 }}%"></div>
    </div>

    <dl class="grid grid-cols-4 gap-2 mt-4 text-center">
        @foreach ($stats as [$nama, $nilai, $kelas])
            <div>
                <dt class="text-[10px] uppercase tracking-wide text-gray-400">{{ $nama }}</dt>
                <dd class="text-sm font-semibold {{ $kelas }}">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>

    <a href="{{ $tautan }}"
        class="inline-flex items-center gap-1 mt-4 text-xs font-medium text-[#1F3864] hover:underline">
        Lihat pegawai bagian ini
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </a>
</div>
