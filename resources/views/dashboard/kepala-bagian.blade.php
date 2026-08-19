{{-- resources/views/dashboard/kepala-bagian.blade.php --}}
<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Greeting card --}}
        <div class="bg-[#1F3864] rounded-2xl p-6 mb-6 flex justify-between items-center text-white">
            <div>
                <h1 class="text-xl font-semibold">{{ auth()->user()->name }}</h1>
                <p class="text-white/70 text-sm mt-0.5">Kepala Seksi &middot; {{ $bagian->nama_bagian }} &middot; {{ $staf->count() }} staf</p>
            </div>
            <a href="{{ route('kabag.tugas.create') }}"
               class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors backdrop-blur-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Beri Tugas
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Staf</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $staf->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu Persetujuan</p>
                        <p class="text-2xl font-semibold {{ $menungguApproval > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $menungguApproval }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Bagian</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $bagian->nama_bagian }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('kabag.tugas.index') }}"
               class="bg-white border border-gray-200 rounded-xl p-5 hover:border-[#1F3864]/30 hover:shadow-sm transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#DCE6F1] flex items-center justify-center group-hover:bg-[#1F3864] transition-colors">
                        <svg class="w-5 h-5 text-[#1F3864] group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Tugas Tim</p>
                        <p class="text-sm text-gray-500">Buat dan kelola tugas untuk staf</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('kabag.approval.index') }}"
               class="bg-white border border-gray-200 rounded-xl p-5 hover:border-[#1F3864]/30 hover:shadow-sm transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#DCE6F1] flex items-center justify-center group-hover:bg-[#1F3864] transition-colors">
                        <svg class="w-5 h-5 text-[#1F3864] group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Persetujuan Laporan</p>
                        <p class="text-sm text-gray-500">Setujui atau kembalikan laporan staf</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</x-app-layout>
