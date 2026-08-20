{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>
    
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4">
            <div class="mb-8 flex flex-col md:flex-row gap-4">
                <!-- Card: Total User -->
                <div class="flex-1 bg-white shadow-sm rounded-xl p-6 flex items-center gap-4 border border-gray-100">
                    <div class="bg-indigo-100 text-indigo-600 rounded-full p-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m8-8a4 4 0 11-8 0 4 4 0 018 0zm6 8V7a2 2 0 00-2-2h-3.5M7 5H4a2 2 0 00-2 2v9a2 2 0 002 2h.5"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $totalUser }}
                        </h3>
                        <div class="text-gray-600 text-sm">Total User</div>
                    </div>
                </div>
                <!-- Card: Total Bagian -->
                <div class="flex-1 bg-white shadow-sm rounded-xl p-6 flex items-center gap-4 border border-gray-100">
                    <div class="bg-green-100 text-green-600 rounded-full p-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M7 10V6a2 2 0 012-2h6a2 2 0 012 2v4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $totalBagian }}
                        </h3>
                        <div class="text-gray-600 text-sm">Total Bagian</div>
                    </div>
                </div>
                <!-- Card: Total Kepala Bagian -->
                <div class="flex-1 bg-white shadow-sm rounded-xl p-6 flex items-center gap-4 border border-gray-100">
                    <div class="bg-yellow-100 text-yellow-500 rounded-full p-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7a4 4 0 018 0v1a4 4 0 01-4 4H8V7z"/>
                            <circle cx="12" cy="17" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $totalKepalaBagian }}
                        </h3>
                        <div class="text-gray-600 text-sm">Total Kepala Bagian</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Data User (Tabel Ringkas) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-lg mb-3 text-indigo-700">10 User Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="border-b text-gray-500">
                                <tr>
                                    <th class="py-2 px-2">Nama</th>
                                    <th class="py-2 px-2">Role</th>
                                    <th class="py-2 px-2">Bagian</th>
                                    <th class="py-2 px-2">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersTerbaru as $user)
                                <tr>
                                    <td class="py-1.5 px-2 whitespace-nowrap">{{ $user->name }}</td>
                                    <td class="py-1.5 px-2 capitalize">
                                        <span class="inline-block px-2 py-0.5 rounded 
                                            @if($user->role=='admin') bg-indigo-100 text-indigo-500 
                                            @elseif($user->role=='kepala_bps') bg-pink-100 text-pink-600 
                                            @elseif($user->role=='kepala_bagian') bg-yellow-100 text-yellow-800 
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ str_replace('_', ' ', $user->role) }}
                                        </span>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        {{ $user->bagian?->nama_bagian ?? '—' }}
                                    </td>
                                    <td class="py-1.5 px-2">{{ $user->email }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right text-xs mt-2">
                        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline">Lihat semua</a>
                    </div>
                </div>

                <!-- Data Bagian (Tabel Ringkas) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-lg mb-3 text-green-700">Data Bagian</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="border-b text-gray-500">
                                <tr>
                                    <th class="py-2 px-2">Kode</th>
                                    <th class="py-2 px-2">Nama Bagian</th>
                                    <th class="py-2 px-2">Kepala Bagian</th>
                                    <th class="py-2 px-2">Total Staf</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bagianList as $bagian)
                                <tr>
                                    <td class="py-1.5 px-2">{{ $bagian->kode_bagian }}</td>
                                    <td class="py-1.5 px-2">{{ $bagian->nama_bagian }}</td>
                                    <td class="py-1.5 px-2">
                                        {{ optional($bagian->kepalaBagian)->name ?? '—' }}
                                    </td>
                                    <td class="py-1.5 px-2">{{ $bagian->pegawai_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right text-xs mt-2">
                        <a href="{{ route('admin.bagian.index') }}" class="text-green-600 hover:underline">Kelola Bagian</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>