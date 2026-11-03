{{-- Parameter: $nama, $sub --}}
<div class="flex items-center gap-3">
    <span class="w-9 h-9 rounded-full bg-[#DCE6F1] text-[#1F3864] text-sm font-semibold flex items-center justify-center shrink-0">
        {{ strtoupper(substr($nama, 0, 1)) }}
    </span>
    <div class="min-w-0">
        <p class="font-medium text-gray-900 truncate">{{ $nama }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $sub }}</p>
    </div>
</div>