{{-- Wajib: $aksi, $cari (placeholder), $filter, $adaFilter
     $filter = [['name' => 'role', 'semua' => 'Semua role', 'opsi' => ['staf' => 'Staf', ...]], ...]
     Pencarian terkirim otomatis setelah berhenti mengetik; pilihan dropdown langsung terkirim. --}}
<form method="GET" action="{{ $aksi }}" role="search" x-data
    x-init="@if (request()->filled('search')) $refs.cari.focus(); $refs.cari.setSelectionRange($refs.cari.value.length, $refs.cari.value.length) @endif"
    class="animate-in mb-4 flex flex-col gap-2.5 rounded-2xl border border-gray-200 bg-white p-3 lg:flex-row lg:items-center"
    style="animation-delay: .2s">

    <div class="group relative min-w-0 flex-1">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-[#1F3864]"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
        </svg>
        <input x-ref="cari" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
            placeholder="{{ $cari }}" @input.debounce.500ms="$el.form.requestSubmit()"
            class="w-full rounded-xl border-gray-300 py-2 pl-9 text-sm transition focus:border-[#1F3864] focus:ring-4 focus:ring-[#1F3864]/10">
    </div>

    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3 lg:flex lg:items-center">
        @foreach ($filter as $f)
            <select name="{{ $f['name'] }}" aria-label="{{ $f['semua'] }}" @change="$el.form.requestSubmit()"
                class="rounded-xl border-gray-300 py-2 pl-3 pr-8 text-sm transition focus:border-[#1F3864] focus:ring-4 focus:ring-[#1F3864]/10">
                <option value="">{{ $f['semua'] }}</option>
                @foreach ($f['opsi'] as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected((string) request($f['name']) === (string) $nilai)>{{ $teks }}</option>
                @endforeach
            </select>
        @endforeach

        @if ($adaFilter)
            <a href="{{ $aksi }}"
                class="inline-flex items-center justify-center gap-1 rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                Reset
            </a>
        @endif
    </div>

    <button type="submit" class="sr-only">Cari</button>
</form>