{{-- Panel setujui / kembalikan. Parameter: $laporan, $rute ('kabag.approval' | 'kepala-bps.approval').
     Hanya tampil kalau laporan masih "menunggu". Aman dipakai berkali-kali dalam satu halaman. --}}
@if ($laporan->status === 'menunggu')
    @php
        // Penanda supaya hanya form milik laporan ini yang terbuka lagi setelah validasi gagal
        $penanda = $laporan->getRouteKey();
        $bukaTolak = $errors->has('catatan') && old('_laporan') === $penanda;
    @endphp

    <div class="animate-in delay-2 bg-white rounded-xl border-2 border-amber-200 p-5"
        x-data="{ tolak: @js($bukaTolak) }">

        <div class="flex items-center gap-2 mb-3">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse motion-reduce:animate-none"></span>
            <p class="text-sm font-semibold text-gray-900">Menunggu keputusan Anda</p>
        </div>

        {{-- Tombol utama --}}
        <div x-show="!tolak" x-transition class="flex gap-2">
            <form method="POST" action="{{ route($rute . '.proses', $laporan) }}"
                x-data="{ loading: false }" @submit="loading = true" class="flex-1">
                @csrf
                <input type="hidden" name="aksi" value="disetujui">
                <x-submit-button type="submit" variant="success" loading="loading" class="w-full">
                    Setujui
                </x-submit-button>
            </form>

            <button type="button" @click="tolak = true"
                class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Tolak
            </button>
        </div>

        {{-- Form pengembalian: alasan wajib diisi --}}
        <form x-show="tolak" x-cloak x-transition method="POST"
            action="{{ route($rute . '.proses', $laporan) }}"
            x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="aksi" value="ditolak">
            <input type="hidden" name="_laporan" value="{{ $penanda }}">

            <label class="text-sm font-medium text-gray-700 block mb-1.5">
                Alasan pengembalian <span class="text-red-500">*</span>
            </label>
            <textarea name="catatan" rows="3" required maxlength="1000"
                class="w-full rounded-lg text-sm focus:ring-red-500/20 {{ $bukaTolak ? 'border-red-500' : 'border-gray-300' }}"
                placeholder="Jelaskan apa yang perlu diperbaiki...">{{ $bukaTolak ? old('catatan') : '' }}</textarea>
            @if ($bukaTolak)
                <p class="text-sm text-red-600 mt-1">{{ $errors->first('catatan') }}</p>
            @endif

            <div class="flex gap-2 mt-3">
                <x-submit-button type="submit" variant="danger" loading="loading" class="flex-1">
                    Kirim penolakan
                </x-submit-button>
                <button type="button" @click="tolak = false"
                    class="bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-300 text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
@endif