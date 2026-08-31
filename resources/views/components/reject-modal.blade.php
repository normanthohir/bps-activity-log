@props(['action'])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block">
    <button type="button" @click="open = true"
        class="inline-flex items-center gap-1 text-sm font-medium px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        Tolak
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[90] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div @click.outside="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-1">Kembalikan Laporan</h3>
                <p class="text-sm text-gray-500 mb-3">Beri catatan alasan penolakan, supaya pegawai tahu apa yang perlu diperbaiki.</p>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    <input type="hidden" name="aksi" value="ditolak">
                    <textarea name="catatan" rows="3" required placeholder="cth: uraian kegiatan kurang detail..."
                        class="w-full text-sm rounded-lg border-gray-300 focus:ring-red-500/20 focus:border-red-400"></textarea>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false"
                            class="px-3.5 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-3.5 py-2 text-sm font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">
                            Kembalikan Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>