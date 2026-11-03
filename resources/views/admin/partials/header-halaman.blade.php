{{-- Wajib: $judul, $deskripsi, $tombol, $href --}}
<div class="animate-in mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ $judul }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $deskripsi }}</p>
    </div>
    <a href="{{ $href }}"
        class="group inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#1F3864] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#16294a] hover:shadow-md active:scale-95 motion-reduce:transition-none">
        <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-90 motion-reduce:transition-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        {{ $tombol }}
    </a>
</div>