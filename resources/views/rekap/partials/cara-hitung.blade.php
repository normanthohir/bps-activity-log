{{-- Parameter: $pembuka, $komponen = [[judul, bobot, uraian], ...], $catatan = [teks, ...], $predikat --}}
<details class="bg-white rounded-xl border border-gray-200 p-5 group">
    <summary class="cursor-pointer text-sm font-medium text-gray-700 list-none flex items-center justify-between">
        Bagaimana skor dihitung?
        <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </summary>
    <div class="mt-4 text-sm text-gray-600 space-y-3">
        <p>{{ $pembuka }}</p>
        <ul class="space-y-2">
            @foreach ($komponen as [$judul, $bobot, $uraian])
                <li><span class="font-medium text-gray-900">{{ $judul }} ({{ $bobot }}%)</span> &ndash; {{ $uraian }}</li>
            @endforeach
        </ul>
        @foreach ($catatan as $teks)
            <p class="text-xs text-gray-500">{{ $teks }}</p>
        @endforeach
        <p class="text-xs text-gray-500">Predikat: Sangat Baik &ge; 85 &middot; Baik &ge; 70 &middot; Cukup &ge; 55 &middot; Perlu Perhatian &lt; 55.</p>
    </div>
</details>