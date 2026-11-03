{{-- Tombol Per pegawai / Per bagian + kolom cari. Memakai variabel Alpine "tab" dan "cari" dari induknya. --}}
<div class="flex items-center justify-between gap-3 mb-3">
    <div class="inline-flex bg-gray-100 rounded-lg p-1">
        <button type="button" @click="tab = 'pegawai'"
            :class="tab === 'pegawai' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors">
            Per pegawai
        </button>
        <button type="button" @click="tab = 'bagian'"
            :class="tab === 'bagian' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="text-sm font-medium px-4 py-1.5 rounded-md transition-colors">
            Per bagian
        </button>
    </div>

    <div x-show="tab === 'pegawai'" x-cloak class="relative w-full max-w-xs">
        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="text" x-model="cari" placeholder="Cari nama pegawai..."
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
    </div>
</div>